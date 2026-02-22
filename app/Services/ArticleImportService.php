<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Support\ImportResult;
use App\Support\ParsedImport;
use App\Support\PreflightResult;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use ZipArchive;

final class ArticleImportService
{
    /**
     * Parse a ZIP file and return its articles and optional categories.
     */
    public function parseZip(UploadedFile $zip): ParsedImport
    {
        $za = new ZipArchive();
        $za->open($zip->getRealPath(), ZipArchive::RDONLY);

        $categoriesYaml = null;
        $categoriesRaw = $za->getFromName('categories.yaml');
        if ($categoriesRaw !== false) {
            try {
                $categoriesYaml = Yaml::parse($categoriesRaw) ?? [];
            } catch (ParseException) {
                $categoriesYaml = [];
            }
        }

        $articles = [];
        for ($i = 0; $i < $za->numFiles; $i++) {
            $name = $za->getNameIndex($i);
            if (! str_starts_with($name, 'articles/')) {
                continue;
            }

            if (! str_ends_with($name, '.md')) {
                continue;
            }

            $raw = $za->getFromIndex($i);
            if ($raw === false) {
                continue;
            }

            $parsed = $this->parseMarkdownFile($raw);
            if ($parsed !== null) {
                $articles[] = $parsed;
            }
        }

        $za->close();

        return new ParsedImport(articles: $articles, categoriesYaml: $categoriesYaml);
    }

    /**
     * Check which category slugs from article frontmatters don't exist in the DB.
     */
    public function preflightCategories(ParsedImport $parsed): PreflightResult
    {
        $referencedSlugs = collect($parsed->articles)
            ->pluck('frontmatter.category')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($referencedSlugs)) {
            return new PreflightResult(ok: true, missingCategorySlugs: []);
        }

        $existingSlugs = Category::query()
            ->whereIn('slug', $referencedSlugs)
            ->pluck('slug')
            ->all();

        $missing = array_values(array_diff($referencedSlugs, $existingSlugs));

        return new PreflightResult(ok: $missing === [], missingCategorySlugs: $missing);
    }

    /**
     * Import categories from a parsed categories.yaml array.
     * Creates missing categories by slug; skips existing ones.
     * Processes root categories first, then children.
     */
    public function importCategories(array $categoriesYaml): void
    {
        // Split into root and children
        $roots = array_filter($categoriesYaml, fn (array $row): bool => empty($row['parent_slug']));
        $children = array_filter($categoriesYaml, fn (array $row): bool => ! empty($row['parent_slug']));

        foreach ($roots as $row) {
            $this->upsertCategory($row, null);
        }

        foreach ($children as $row) {
            $parent = Category::query()->where('slug', $row['parent_slug'])->first();
            $this->upsertCategory($row, $parent?->id);
        }
    }

    /**
     * Import articles from a parsed ZIP.
     */
    public function import(ParsedImport $parsed, string $duplicateStrategy, int $userId): ImportResult
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Build slug→id map for categories
        $categoryMap = Category::query()->pluck('id', 'slug')->all();

        foreach ($parsed->articles as $entry) {
            $frontmatter = $entry['frontmatter'];
            $content = $entry['content'];
            $slug = $frontmatter['slug'] ?? null;

            if (empty($slug)) {
                continue;
            }

            try {
                $existing = Article::query()->where('slug', $slug)->first();

                if ($existing !== null && $duplicateStrategy === 'skip') {
                    $skipped++;

                    continue;
                }

                $isDraft = (bool) ($frontmatter['draft'] ?? false);
                $status = $isDraft ? Status::Draft : Status::Published;

                $publishedAt = null;
                if (! $isDraft && ! empty($frontmatter['date'])) {
                    $publishedAt = \Carbon\Carbon::parse($frontmatter['date'])->startOfSecond();
                }

                $meta = array_filter([
                    'meta_title' => $frontmatter['meta_title'] ?? null,
                    'meta_description' => $frontmatter['meta_description'] ?? null,
                    'featured_image_url' => $frontmatter['featured_image_url'] ?? null,
                    'featured_image_caption' => $frontmatter['featured_image_caption'] ?? null,
                    'featured_image_alt' => $frontmatter['featured_image_alt'] ?? null,
                    'og_image' => $frontmatter['og_image'] ?? null,
                ], fn ($v): bool => $v !== null);

                $attributes = [
                    'user_id' => $userId,
                    'title' => $frontmatter['title'] ?? 'Untitled',
                    'slug' => $slug,
                    'content' => $content,
                    'summary' => $frontmatter['description'] ?? null,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'last_edited_at' => empty($frontmatter['last_edited_at'])
                        ? null
                        : \Carbon\Carbon::parse($frontmatter['last_edited_at'])->startOfSecond(),
                    'past_slugs' => $frontmatter['past_slugs'] ?? [],
                    'category_id' => isset($frontmatter['category'])
                        ? ($categoryMap[$frontmatter['category']] ?? null)
                        : null,
                    'meta' => $meta ?: null,
                ];

                Article::withoutEvents(function () use ($existing, $attributes, $duplicateStrategy): void {
                    if ($existing !== null && $duplicateStrategy === 'overwrite') {
                        $existing->forceFill($attributes)->save();
                    } else {
                        Article::query()->forceCreate($attributes);
                    }
                });

                $imported++;
            } catch (Throwable $e) {
                $errors[$slug] = $e->getMessage();
            }
        }

        return new ImportResult(imported: $imported, skipped: $skipped, errors: $errors);
    }

    /**
     * Parse a raw Markdown file string into frontmatter and content.
     *
     * @return array{frontmatter: array<string, mixed>, content: string}|null
     */
    private function parseMarkdownFile(string $raw): ?array
    {
        // Files start with ---\n, split on the closing ---
        if (! str_starts_with($raw, "---\n")) {
            return null;
        }

        $withoutOpening = mb_substr($raw, 4);
        $closingPos = mb_strpos($withoutOpening, "\n---\n");

        if ($closingPos === false) {
            return null;
        }

        $yamlPart = mb_substr($withoutOpening, 0, $closingPos);
        $body = mb_ltrim(mb_substr($withoutOpening, $closingPos + 5));

        try {
            $frontmatter = Yaml::parse($yamlPart) ?? [];
        } catch (ParseException) {
            return null;
        }

        return ['frontmatter' => $frontmatter, 'content' => $body];
    }

    /**
     * Insert or skip a single category row.
     */
    private function upsertCategory(array $row, ?int $parentId): void
    {
        $slug = $row['slug'] ?? null;
        if (empty($slug)) {
            return;
        }

        Category::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $row['name'] ?? $slug,
                'description' => $row['description'] ?? null,
                'parent_id' => $parentId,
            ],
        );
    }
}

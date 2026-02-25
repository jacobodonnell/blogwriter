<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;
use App\Support\ImportResult;
use App\Support\ParsedImport;
use App\Support\PreflightResult;
use Carbon\Carbon;
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

        $categoriesYaml = $this->extractYaml($za, 'categories.yaml');
        $settingsYaml = $this->extractYaml($za, 'settings.yaml');
        $photosYaml = $this->extractYaml($za, 'photos.yaml');

        $articles = [];
        $imagesTempDir = null;

        for ($i = 0; $i < $za->numFiles; $i++) {
            $name = $za->getNameIndex($i);

            if (str_starts_with($name, 'images/')) {
                $basename = basename($name);
                if ($basename !== '') {
                    if ($imagesTempDir === null) {
                        $imagesTempDir = sys_get_temp_dir().'/bw-import-'.uniqid();
                        mkdir($imagesTempDir);
                    }
                    file_put_contents($imagesTempDir.'/'.$basename, $za->getFromIndex($i));
                }

                continue;
            }

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

        return new ParsedImport(
            articles: $articles,
            categoriesYaml: $categoriesYaml,
            settingsYaml: $settingsYaml,
            photosYaml: $photosYaml,
            imagesTempDir: $imagesTempDir,
        );
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
     *
     * H1 headings in body content are silently normalised to H2 on import.
     * The article title already renders as H1 in the theme, so H1s in body
     * content would create duplicate top-level headings. This mirrors the
     * NoH1Heading validation rule enforced in the editor.
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
            $content = preg_replace('/^# (?!#)/m', '## ', $entry['content']);
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
                    $publishedAt = Carbon::parse($frontmatter['date'])->startOfSecond();
                }

                $meta = array_filter([
                    'meta_title' => $frontmatter['meta_title'] ?? null,
                    'meta_description' => $frontmatter['meta_description'] ?? null,
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
                        : Carbon::parse($frontmatter['last_edited_at'])->startOfSecond(),
                    'created_at' => empty($frontmatter['created_at'])
                        ? now()
                        : Carbon::parse($frontmatter['created_at']),
                    'past_slugs' => $frontmatter['past_slugs'] ?? [],
                    'category_id' => isset($frontmatter['category'])
                        ? ($categoryMap[$frontmatter['category']] ?? null)
                        : null,
                    'external_featured_img_url' => $frontmatter['featured_image_url'] ?? null,
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

        // Reconnect featured photos by photo_slug after all articles are saved
        foreach ($parsed->articles as $entry) {
            $photoSlug = $entry['frontmatter']['photo_slug'] ?? null;
            if (empty($photoSlug)) {
                continue;
            }

            $slug = $entry['frontmatter']['slug'] ?? null;
            if (empty($slug)) {
                continue;
            }

            $article = Article::query()->where('slug', $slug)->first();
            $photo = Photo::query()->where('slug', $photoSlug)->first();
            if ($article !== null && $photo !== null) {
                $article->forceFill(['photo_id' => $photo->id])->save();
            }
        }

        return new ImportResult(imported: $imported, skipped: $skipped, errors: $errors);
    }

    /**
     * Import photos from a parsed photos.yaml + extracted image files.
     */
    public function importPhotos(ParsedImport $parsed, string $duplicateStrategy, int $userId): void
    {
        if ($parsed->photosYaml === null) {
            return;
        }

        $categoryMap = Category::query()->pluck('id', 'slug')->all();

        foreach ($parsed->photosYaml as $row) {
            $slug = $row['slug'] ?? null;
            if (empty($slug)) {
                continue;
            }

            $existing = Photo::query()->where('slug', $slug)->first();
            if ($existing !== null && $duplicateStrategy === 'skip') {
                continue;
            }

            $photo = $existing ?? new Photo();
            $photo->forceFill([
                'user_id' => $userId,
                'slug' => $slug,
                'filename' => $row['filename'] ?? $slug,
                'caption' => $row['caption'] ?? null,
                'alt_text' => $row['alt_text'] ?? '',
                'status' => Status::tryFrom($row['status'] ?? '') ?? Status::Draft,
                'published_at' => empty($row['published_at']) ? null : Carbon::parse($row['published_at']),
                'taken_at' => empty($row['taken_at']) ? null : Carbon::parse($row['taken_at']),
                'category_id' => isset($row['category']) ? ($categoryMap[$row['category']] ?? null) : null,
                'meta' => $row['meta'] ?? null,
            ])->save();

            if (! empty($row['image_file']) && $parsed->imagesTempDir !== null) {
                $filePath = $parsed->imagesTempDir.'/'.$row['image_file'];
                if (file_exists($filePath) && ($existing === null || $duplicateStrategy === 'overwrite')) {
                    if ($existing !== null) {
                        $photo->clearMediaCollection('image');
                    }
                    $photo->addMedia($filePath)
                        ->preservingOriginal()
                        ->toMediaCollection('image');
                }
            }
        }

        if ($parsed->imagesTempDir !== null && is_dir($parsed->imagesTempDir)) {
            array_map('unlink', glob($parsed->imagesTempDir.'/*') ?: []);
            rmdir($parsed->imagesTempDir);
        }
    }

    /**
     * Apply settings from a parsed settings.yaml array with graceful degradation.
     *
     * @param  array<string, mixed>  $settingsYaml
     */
    public function importSettings(array $settingsYaml): void
    {
        $validThemesLight = config('appearance.themes_light');
        $validThemesDark = config('appearance.themes_dark');
        $validFonts = array_keys(config('appearance.fonts'));

        if (isset($settingsYaml['theme_light']) && in_array($settingsYaml['theme_light'], $validThemesLight, true)) {
            Setting::set('theme_light', $settingsYaml['theme_light']);
        }

        if (isset($settingsYaml['theme_dark']) && in_array($settingsYaml['theme_dark'], $validThemesDark, true)) {
            Setting::set('theme_dark', $settingsYaml['theme_dark']);
        }

        if (isset($settingsYaml['theme_font']) && in_array($settingsYaml['theme_font'], $validFonts, true)) {
            Setting::set('theme_font', $settingsYaml['theme_font']);
        }

        if (! empty($settingsYaml['profile_name'])) {
            User::query()->update(['name' => $settingsYaml['profile_name']]);
        }

        $stringKeys = [
            'profile_email', 'profile_avatar', 'profile_bio',
            'profile_github', 'profile_mastodon', 'profile_bluesky',
            'page_home_subtitle', 'page_articles_subtitle', 'page_photos_subtitle',
        ];

        foreach ($stringKeys as $key) {
            if (isset($settingsYaml[$key])) {
                Setting::set($key, $settingsYaml[$key]);
            }
        }
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
     * Extract and parse a YAML file from the ZIP archive.
     *
     * @return array<mixed>|null
     */
    private function extractYaml(ZipArchive $za, string $filename): ?array
    {
        $raw = $za->getFromName($filename);

        if ($raw === false) {
            return null;
        }

        try {
            return Yaml::parse($raw) ?? [];
        } catch (ParseException) {
            return [];
        }
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

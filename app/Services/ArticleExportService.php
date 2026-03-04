<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Yaml\Yaml;
use ZipStream\ZipStream;

final class ArticleExportService
{
    /**
     * Stream all articles as a ZIP of Markdown files into the given ZipStream.
     *
     * @param  Collection<int, Article>  $articles
     */
    public function streamToZip(ZipStream $zip, Collection $articles): void
    {
        foreach ($articles as $article) {
            $zip->addFile(sprintf('articles/%s.md', $article->slug), $this->buildArticleMarkdown($article));
        }
    }

    /**
     * Build the complete Markdown file content (frontmatter + body) for a single article.
     */
    public function buildArticleMarkdown(Article $article): string
    {
        $frontmatter = $this->buildFrontmatter($article);
        $content = $article->getAttributes()['content'] ?? '';

        return "---\n".Yaml::dump($frontmatter, 2, 2)."---\n\n".$content;
    }

    /**
     * Stream all categories as a YAML file into the given ZipStream.
     */
    public function streamCategoriesToZip(ZipStream $zip): void
    {
        $categories = Category::query()->with('parent')->orderBy('id')->get();

        $data = $categories->map(fn (Category $category): array => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_slug' => $category->parent?->slug,
        ])->values()->all();

        $zip->addFile('categories.yaml', Yaml::dump($data, 2, 2));
    }

    /**
     * Stream a single article's category (with ancestor chain) as categories.yaml into the ZipStream.
     *
     * Does nothing if the article has no category.
     */
    public function streamArticleCategoryToZip(ZipStream $zip, Article $article): void
    {
        if (! $article->category) {
            return;
        }

        $article->category->loadMissing('parent');
        $ancestors = $article->category->ancestors();
        $ancestors->each(fn (Category $c) => $c->loadMissing('parent'));
        $ancestors->push($article->category);

        $data = $ancestors->map(fn (Category $category): array => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_slug' => $category->parent?->slug,
        ])->values()->all();

        $zip->addFile('categories.yaml', Yaml::dump($data, 2, 2));
    }

    /**
     * Stream site settings as a settings.yaml file into the given ZipStream.
     */
    public function streamSettingsToZip(ZipStream $zip): void
    {
        $settingKeys = [
            'theme_light', 'theme_dark', 'theme_font',
            'profile_email', 'profile_avatar', 'profile_bio',
            'profile_github', 'profile_mastodon', 'profile_bluesky',
            'page_home_subtitle', 'page_articles_subtitle', 'page_photos_subtitle',
        ];

        $data = [
            'profile_name' => User::query()->value('name'),
            ...Setting::query()->whereIn('key', $settingKeys)->pluck('value', 'key')->all(),
        ];

        $zip->addFile('settings.yaml', Yaml::dump($data, 2, 2));
    }

    /**
     * Build the YAML frontmatter array for an article.
     *
     * @return array<string, mixed>
     */
    public function buildFrontmatter(Article $article): array
    {
        $frontmatter = [
            'title' => $article->title,
            'date' => $article->published_at?->utc()->toIso8601String()
                                        ?? $article->created_at->utc()->toIso8601String(),
            'created_at' => $article->created_at->utc()->toIso8601String(),
            'last_edited_at' => $article->last_edited_at?->utc()->toIso8601String(),
            'slug' => $article->slug,
            'status' => $article->status->value,
            'description' => $article->summary ?: null,
            'category' => $article->category?->slug,
            'past_slugs' => array_values($article->past_slugs ?? []),
            'meta_title' => $article->meta['meta_title'] ?? null,
            'meta_description' => $article->meta['meta_description'] ?? null,
            'featured_image_url' => $article->external_featured_img_url,
            'photo_slug' => $article->featuredPhoto?->slug,
            'featured_image_caption' => $article->featured_image_caption ?: null,
            'featured_image_alt' => $article->meta['featured_image_alt']
                                        ?? $article->featuredPhoto?->alt_text
                                        ?: null,
            'og_image' => $article->meta['og_image'] ?? null,
        ];

        return array_filter($frontmatter, fn ($value): bool => $value !== null && $value !== []);
    }
}

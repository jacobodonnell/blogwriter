<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
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
            $frontmatter = $this->buildFrontmatter($article);
            $content = $article->getAttributes()['content'] ?? '';
            $fileContent = "---\n".Yaml::dump($frontmatter, 2, 2)."---\n\n".$content;

            $zip->addFile("articles/{$article->slug}.md", $fileContent);
        }
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
            'slug' => $article->slug,
            'draft' => $article->status->value === 'draft',
            'description' => $article->summary ?: null,
            'author' => $article->user?->name,
            'category' => $article->category?->slug,
            'past_slugs' => $article->past_slugs ?? [],
            'meta_title' => $article->meta['meta_title'] ?? null,
            'meta_description' => $article->meta['meta_description'] ?? null,
            'featured_image_url' => $article->meta['featured_image_url'] ?? null,
        ];

        return array_filter($frontmatter, fn ($value): bool => $value !== null);
    }
}

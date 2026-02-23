<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateUniqueSlugAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticlePreviewRequest;
use App\Models\Article;
use Illuminate\View\View;

final class ArticlePreviewController extends Controller
{
    /**
     * Update article for live preview (AJAX auto-save).
     *
     * All changes are stored in the `draft` JSON column — live columns are never touched.
     */
    public function __invoke(UpdateArticlePreviewRequest $request, Article $article): View
    {
        $data = $request->validated();
        $draft = $article->draft ?? [];

        // Translate featured_image URL into meta before merging into draft
        $featuredImageUrl = $data['featured_image'] ?? null;
        unset($data['featured_image']);

        // Merge validated fields into draft snapshot (except status — intentional actions only)
        foreach ($data as $key => $value) {
            if ($key === 'status') {
                continue;
            }

            $draft[$key] = $value;
        }

        // Apply featured_image URL to draft meta
        if (! empty($featuredImageUrl)) {
            $meta = $draft['meta'] ?? $article->meta ?? [];
            $meta['featured_image_url'] = $featuredImageUrl;
            $draft['meta'] = $meta;
            $draft['photo_id'] = null;
        }

        // Slug generation within draft: resolve placeholders using draft title
        if (isset($draft['slug']) && $draft['slug'] !== '') {
            $baseSlug = Article::isPlaceholderSlug((string) $draft['slug']) && ! empty($draft['title'])
                ? $draft['title']
                : $draft['slug'];

            $newSlug = app(GenerateUniqueSlugAction::class)
                ->handle($baseSlug, Article::class, $article->id);

            $draft['slug'] = $newSlug;
        }

        // Mutual exclusion: photo_id clears featured_image_url
        if (! empty($draft['photo_id'])) {
            $meta = $draft['meta'] ?? $article->meta ?? [];
            unset($meta['featured_image_url']);
            $draft['meta'] = $meta;
        }

        $article->update(['draft' => $draft]);

        $article->refresh()->load('category')->applyDraft();

        return view('admin.articles.preview', ['article' => $article]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateUniqueSlugAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticlePreviewRequest;
use App\Models\Article;
use App\Models\Category;
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
        $draft = [];

        // Handle featured_image URL — use request->has() to detect "sent but empty" (cleared)
        $featuredImageSent = $request->has('featured_image');
        $featuredImageUrl = $data['featured_image'] ?? null;
        unset($data['featured_image']);

        // Only store fields that differ from the live DB values
        foreach ($data as $key => $value) {
            if (in_array($key, ['status', 'meta'], true)) {
                continue;
            }

            if ($this->valuesDiffer($value, $article->getOriginal($key))) {
                $draft[$key] = $value;
            }
        }

        // Build draft meta: only store meta keys that differ from live
        $liveMeta = $article->getOriginal('meta') ?? [];
        $incomingMeta = $data['meta'] ?? [];
        $draftMeta = [];

        foreach ($incomingMeta as $metaKey => $metaValue) {
            $liveValue = $liveMeta[$metaKey] ?? null;

            if ($this->valuesDiffer($metaValue, $liveValue)) {
                $draftMeta[$metaKey] = $metaValue;
            }
        }

        // Handle external featured image URL (sent but null/empty = user cleared it → null)
        if ($featuredImageSent) {
            $effectiveUrl = $featuredImageUrl ?: null;
            $liveUrl = $article->getOriginal('external_featured_img_url');

            if ($this->valuesDiffer($effectiveUrl, $liveUrl)) {
                $draft['external_featured_img_url'] = $effectiveUrl;
            }

            // Mutual exclusion: URL set → null out photo_id (only if live has one)
            if ($effectiveUrl !== null && $article->getOriginal('photo_id') !== null) {
                $draft['photo_id'] = null;
            }
        }

        // Mutual exclusion: photo_id clears external_featured_img_url
        if (! empty($draft['photo_id'])) {
            $draft['external_featured_img_url'] = null;
        }

        if ($draftMeta !== []) {
            $draft['meta'] = $draftMeta;
        }

        // Slug generation: use the incoming slug (even if it matched live) for placeholder resolution
        $incomingSlug = $data['slug'] ?? null;

        if ($incomingSlug !== null && $incomingSlug !== '') {
            $draftTitle = $draft['title'] ?? $article->title;
            $baseSlug = Article::isPlaceholderSlug((string) $incomingSlug) && ! empty($draftTitle)
                ? $draftTitle
                : ($draft['slug'] ?? $incomingSlug);

            $newSlug = app(GenerateUniqueSlugAction::class)
                ->handle($baseSlug, Article::class, $article->id);

            if ($this->valuesDiffer($newSlug, $article->getOriginal('slug'))) {
                $draft['slug'] = $newSlug;
            } else {
                unset($draft['slug']);
            }
        }

        $article->update(['draft' => $draft === [] ? null : $draft]);

        $article->refresh()->load('category')->applyDraft();

        // Re-resolve category relation if draft changed category_id
        if ($article->category_id !== $article->getOriginal('category_id')) {
            $article->setRelation('category', Category::find($article->category_id));
        }

        return view('admin.articles.preview', ['article' => $article]);
    }

    private function valuesDiffer(mixed $incoming, mixed $live): bool
    {
        $normalized = static fn (mixed $value): string => (string) ($value ?? '');

        return $normalized($incoming) !== $normalized($live);
    }
}

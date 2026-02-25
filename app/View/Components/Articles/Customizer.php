<?php

declare(strict_types=1);

namespace App\View\Components\Articles;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

final class Customizer extends Component
{
    public function __construct(
        public Article $article,
        public Collection $categories,
        public Collection $photos,
        public bool $isNew = false,
        private readonly string $liveContent = '',
        private readonly string $liveTitle = '',
        private readonly string $liveSlug = '',
        private readonly string $liveSummary = '',
        private readonly ?int $liveCategoryId = null,
        private readonly ?int $livePhotoId = null,
        private readonly ?string $liveExternalFeaturedImgUrl = null,
        private readonly array $liveMeta = [],
    ) {}

    /** @return array<string, mixed> */
    public function alpineConfig(): array
    {
        return [
            'title' => old('title', $this->article->title ?? ''),
            'slug' => old('slug', $this->article->slug ?? ''),
            'content' => old('content', $this->article->content ?? ''),
            'summary' => old('summary', $this->article->summary ?? ''),
            'categoryId' => old('category_id', $this->article->category_id) ?? '',
            'metaTitle' => old('meta.meta_title', $this->article->meta['meta_title'] ?? ''),
            'metaDescription' => old('meta.meta_description', $this->article->meta['meta_description'] ?? ''),
            'ogImage' => old('meta.og_image', $this->article->meta['og_image'] ?? ''),
            'selectedPhotoId' => old('photo_id', $this->article->photo_id ?? ''),
            'featuredImageUrl' => old('featured_image', $this->article->external_featured_img_url ?? ''),
            'showUrlField' => ! empty(old('featured_image', $this->article->external_featured_img_url ?? '')),
            'featuredImageCaption' => old('meta.featured_image_caption', $this->article->meta['featured_image_caption'] ?? ''),
            'usePhotoCaption' => old('meta.use_photo_caption', ! empty($this->article->meta['use_photo_caption'])),
            'initialStatus' => $this->article->status->value,
            'currentStatus' => $this->article->status->value,
            'wasEverPublished' => $this->article->published_at !== null,
            'originalPublishedAt' => $this->article->published_at?->format('F j, Y'),
            'saveRoute' => $this->isNew
                ? route('admin.articles.store')
                : route('admin.articles.update', $this->article),
            'isNew' => $this->isNew,
            'hasDraft' => $this->article->exists && $this->article->hasDraft(),
            'savedContent' => $this->liveContent ?: ($this->article->content ?? ''),
            'savedTitle' => $this->liveTitle ?: ($this->article->title ?? ''),
            'savedSlug' => $this->liveSlug ?: ($this->article->slug ?? ''),
            'savedSummary' => $this->liveSummary ?: ($this->article->summary ?? ''),
            'savedCategoryId' => (string) ($this->liveCategoryId ?? $this->article->category_id ?? ''),
            'savedPhotoId' => (string) ($this->livePhotoId ?? $this->article->photo_id ?? ''),
            'savedFeaturedImageUrl' => $this->liveExternalFeaturedImgUrl ?? '',
            'savedFeaturedImageCaption' => $this->liveMeta['featured_image_caption'] ?? '',
            'savedUsePhotoCaption' => ! empty($this->liveMeta['use_photo_caption'] ?? false),
            'savedMetaTitle' => $this->liveMeta['meta_title'] ?? '',
            'savedMetaDescription' => $this->liveMeta['meta_description'] ?? '',
            'savedOgImage' => $this->liveMeta['og_image'] ?? '',
            'contentHistory' => $this->article->content_history ?? ['entries' => [], 'pointer' => 0],
        ];
    }

    public function render(): View
    {
        return view('components.articles.customizer.customizer');
    }
}

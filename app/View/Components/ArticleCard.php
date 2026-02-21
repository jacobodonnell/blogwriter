<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class ArticleCard extends Component
{
    public bool $isStacked;

    public string $articleUrl;

    public string $articleImage;

    public string $titleClass;

    public string $clampClass;

    public function __construct(
        public Article $article,
        public bool $stacked = false,
        public bool $featured = false,
        public bool $odd = true,
        public string $authorName = '',
        public ?string $placeholderUrl = null,
    ) {
        $this->placeholderUrl ??= placeholder_image_url();
        $this->isStacked = $this->featured || $this->stacked;
        $this->articleUrl = route('articles.show', $this->article->slug);
        $this->articleImage = $this->article->featured_image_url ?? $this->placeholderUrl ?? '';
        $this->titleClass = match (true) {
            $this->featured => 'text-2xl md:text-3xl',
            ! $this->isStacked => 'text-xl',
            default => 'text-lg',
        };
        $this->clampClass = $this->featured ? 'line-clamp-4' : 'line-clamp-3';
    }

    public function render(): View
    {
        return view('components.article-card');
    }
}

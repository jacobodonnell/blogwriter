<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\FeedItem;
use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use App\Support\Markdown;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class FeedService
{
    /**
     * Get feed items from published articles and photos.
     *
     * @return Collection<int, FeedItem>
     */
    public function getItems(): Collection
    {
        $authorName = $this->getAuthorName();

        $articles = Article::published()
            ->with(['category', 'featuredPhoto'])
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn (Article $article): FeedItem => new FeedItem(
                title: $article->title,
                url: $article->permalink(),
                id: $article->permalink(),
                contentHtml: Markdown::render($article->content ?? ''),
                summary: $article->excerpt,
                authorName: $authorName,
                publishedAt: $article->published_at,
                updatedAt: $article->last_edited_at,
                imageUrl: $article->featured_image_url,
                categoryName: $article->category?->name,
                contentType: 'article',
            ));

        $photos = Photo::published()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn (Photo $photo): FeedItem => new FeedItem(
                title: $this->resolvePhotoTitle($photo),
                url: route('photos.show', $photo->slug),
                id: route('photos.show', $photo->slug),
                contentHtml: $this->buildPhotoHtml($photo),
                summary: $this->resolvePhotoSummary($photo),
                authorName: $authorName,
                publishedAt: $photo->published_at,
                categoryName: $photo->category?->name,
                contentType: 'photo',
            ));

        return $articles->concat($photos)
            ->sortByDesc(fn (FeedItem $item): int => $item->publishedAt->getTimestamp())
            ->take(20)
            ->values();
    }

    /**
     * Get the most recent update timestamp from a collection of feed items.
     *
     * @param  Collection<int, FeedItem>  $items
     */
    public function getLastUpdated(Collection $items): ?CarbonInterface
    {
        if ($items->isEmpty()) {
            return null;
        }

        return $items->reduce(function (?CarbonInterface $carry, FeedItem $item): CarbonInterface {
            $date = $item->updatedAt ?? $item->publishedAt;

            return ! $carry instanceof CarbonInterface || $date->greaterThan($carry) ? $date : $carry;
        });
    }

    /**
     * Get the site author name.
     */
    private function getAuthorName(): string
    {
        return User::first()?->name ?? config('app.name', 'BlogWriter');
    }

    /**
     * Resolve the title for a photo feed item.
     */
    private function resolvePhotoTitle(Photo $photo): string
    {
        if ($photo->alt_text) {
            return $photo->alt_text;
        }

        if ($photo->caption) {
            return Str::limit(strip_tags(Markdown::render($photo->caption)), 80);
        }

        return 'Photo';
    }

    /**
     * Resolve the summary for a photo feed item.
     */
    private function resolvePhotoSummary(Photo $photo): string
    {
        if (! $photo->caption) {
            return '';
        }

        return Str::limit(strip_tags(Markdown::render($photo->caption)), 255);
    }

    /**
     * Build HTML content for a photo feed item.
     */
    private function buildPhotoHtml(Photo $photo): string
    {
        $html = '';

        if ($photo->image_url) {
            $alt = e($photo->alt_text ?? '');
            $html .= '<img src="'.e($photo->image_url).'" alt="'.$alt.'" />';
        }

        if ($photo->caption) {
            $html .= Markdown::render($photo->caption);
        }

        return $html;
    }
}

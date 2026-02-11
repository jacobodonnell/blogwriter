<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'status',
        'published_at',
        'last_edited_at',
        'featured_image',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'past_slugs' => 'array',
            'published_at' => 'datetime',
            'last_edited_at' => 'datetime',
        ];
    }

    /**
     * Set the published_at attribute, stripping microseconds for database consistency.
     */
    public function setPublishedAtAttribute($value): void
    {
        if ($value !== null) {
            $value = \Carbon\Carbon::parse($value)->startOfSecond();
        }
        $this->attributes['published_at'] = $value;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($article): void {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }

            if ($article->isDirty('slug') && ! empty($article->getOriginal('slug'))) {
                $article->addPastSlug($article->getOriginal('slug'));
            }

            // Handle status transitions for published_at
            if ($article->isDirty('status')) {
                // When status changes TO 'published' and published_at is null, set it to now
                if ($article->status === 'published' && is_null($article->published_at)) {
                    $article->published_at = now()->startOfSecond();
                }
            }

            // When saving an article that was already published before (has original published_at),
            // track the edit time. This handles both:
            // 1. Editing an already-published article (status stays published)
            // 2. Re-publishing a previously-published article (status changes from draft to published)
            // Only set last_edited_at if:
            // 1. Status is 'published'
            // 2. The article was already published before this save (has original published_at)
            // 3. last_edited_at hasn't been manually set already
            if ($article->status === 'published'
                && ! is_null($article->getOriginal('published_at'))
                && is_null($article->last_edited_at)) {
                $article->last_edited_at = now()->startOfSecond();
            }
        });
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Scope for published articles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function published($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft articles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function draft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for hidden articles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function hidden($query)
    {
        return $query->where('status', 'hidden');
    }

    /**
     * Scope for articles visible to public.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function visibleToPublic($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for articles visible to owner.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function visibleToOwner($query)
    {
        return $query->whereIn('status', ['published', 'hidden']);
    }

    /**
     * Get the HTML content rendered from markdown with XSS protection.
     */
    public function getContentHtmlAttribute(): string
    {
        return Str::markdown($this->content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Get the excerpt - either summary or first 255 chars of content.
     */
    public function getExcerptAttribute(): string
    {
        if (! empty($this->summary)) {
            return $this->summary;
        }

        return Str::limit(strip_tags($this->content), 255);
    }

    /**
     * Get the estimated reading time in minutes (200 wpm).
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));

        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Get the meta title or fallback to article title.
     */
    public function getMetaTitleAttribute(): string
    {
        return $this->meta['meta_title'] ?? $this->title;
    }

    /**
     * Get the meta description or fallback to excerpt.
     */
    public function getMetaDescriptionAttribute(): string
    {
        return $this->meta['meta_description'] ?? $this->excerpt;
    }

    /**
     * Get the Open Graph image or fallback to featured image.
     */
    public function getOgImageAttribute(): ?string
    {
        return $this->meta['og_image'] ?? $this->featured_image;
    }

    /**
     * Get the permalink for this article.
     */
    public function permalink(): string
    {
        // Check if public route exists, otherwise return preview URL
        if (\Illuminate\Support\Facades\Route::has('article.show')) {
            return route('article.show', $this->slug);
        }

        // Return admin preview URL if public route doesn't exist yet
        return url('/blog/'.$this->slug);
    }

    /**
     * Check if the article has been edited after initial publish.
     */
    public function wasEdited(): bool
    {
        return ! is_null($this->last_edited_at);
    }

    /**
     * Check if the article is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null && $this->published_at <= now();
    }

    /**
     * Publish the article.
     */
    public function publish(): void
    {
        $this->status = 'published';
        $this->published_at = now();
        $this->save();
    }

    /**
     * Unpublish the article (set to draft).
     */
    public function unpublish(): void
    {
        $this->status = 'draft';
        $this->save();
    }

    /**
     * Hide the article.
     */
    public function hide(): void
    {
        $this->status = 'hidden';
        $this->save();
    }

    /**
     * Add a slug to the past_slugs array.
     */
    public function addPastSlug(string $slug): void
    {
        $pastSlugs = $this->past_slugs ?? [];

        if (! in_array($slug, $pastSlugs)) {
            $pastSlugs[] = $slug;
            $this->past_slugs = $pastSlugs;
        }
    }

    /**
     * Check if a slug exists in past_slugs.
     */
    public function hasPastSlug(string $slug): bool
    {
        return in_array($slug, $this->past_slugs ?? []);
    }
}

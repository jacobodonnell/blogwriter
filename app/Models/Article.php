<?php

namespace App\Models;

use App\Enums\Status;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'past_slugs' => 'array',
            'published_at' => 'datetime',
            'last_edited_at' => 'datetime',
            'status' => Status::class,
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

    /**
     * Accessor/mutator for content: single newline UX <-> CommonMark double newlines.
     *
     * Mutator: converts single \n to \n\n for paragraph breaks (outside fenced code blocks).
     * Accessor: collapses \n\n back to \n for editor display (outside fenced code blocks).
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null) {
                    return null;
                }

                return $this->processOutsideCodeBlocks($value, function (string $text): string {
                    $placeholder = "\x00TRIPLE_NEWLINE\x00";
                    $text = preg_replace('/\n{3,}/', $placeholder, $text);
                    $text = str_replace("\n\n", "\n", $text);

                    return str_replace($placeholder, "\n\n", $text);
                });
            },
            set: function (?string $value): ?string {
                if ($value === null) {
                    return null;
                }

                return $this->processOutsideCodeBlocks($value, function (string $text): string {
                    $placeholder = "\x00DOUBLE_NEWLINE\x00";
                    $text = str_replace("\n\n", $placeholder, $text);
                    $text = str_replace("\n", "\n\n", $text);

                    return str_replace($placeholder, "\n\n", $text);
                });
            },
        );
    }

    /**
     * Apply a callback to text segments outside of fenced code blocks.
     */
    private function processOutsideCodeBlocks(string $text, callable $callback): string
    {
        $parts = preg_split('/(```[\s\S]*?```)/m', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => &$part) {
            if ($i % 2 === 0) {
                $part = $callback($part);
            }
        }

        return implode('', $parts);
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

            // Set published_at when first published
            $newStatus = $article->status;

            if ($article->isDirty('status') && $newStatus === Status::Published && is_null($article->published_at)) {
                $article->published_at = now()->startOfSecond();
            }

            // Mutual exclusion: photo_id and meta.featured_image_url
            $meta = $article->meta ?? [];
            if ($article->photo_id && Arr::has($meta, 'featured_image_url')) {
                Arr::forget($meta, 'featured_image_url');
                $article->meta = $meta;
            }

            // Track edit time for previously-published articles
            if ($newStatus === Status::Published
                && ! is_null($article->getOriginal('published_at'))
                && is_null($article->last_edited_at)) {
                $article->last_edited_at = now()->startOfSecond();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the featured photo for this article.
     *
     * @return BelongsTo<Photo, $this>
     */
    public function featuredPhoto(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the featured image URL — meta URL first, then Photo relationship.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return Arr::get($this->meta ?? [], 'featured_image_url')
            ?? $this->featuredPhoto?->image_url;
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
        return $query->where('status', Status::Published)
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
        return $query->where('status', Status::Draft);
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
        return $query->where('status', Status::Published);
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
        return $query->where('status', Status::Published);
    }

    /**
     * Get the HTML content rendered from markdown with XSS protection.
     */
    public function getContentHtmlAttribute(): string
    {
        return Markdown::render($this->attributes['content'] ?? '');
    }

    /**
     * Get the excerpt - either summary or first 255 chars of content.
     */
    public function getExcerptAttribute(): string
    {
        if (! empty($this->summary)) {
            return $this->summary;
        }

        return Str::limit(strip_tags($this->attributes['content'] ?? ''), 255);
    }

    /**
     * Get the estimated reading time in minutes (200 wpm).
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->attributes['content'] ?? ''));

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
        return $this->meta['og_image'] ?? $this->featured_image_url;
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
        return $this->status === Status::Published && $this->published_at !== null && $this->published_at <= now();
    }

    /**
     * Publish the article.
     */
    public function publish(): void
    {
        $this->status = Status::Published;
        $this->published_at = now();
        $this->save();
    }

    /**
     * Unpublish the article (set to draft).
     */
    public function unpublish(): void
    {
        $this->status = Status::Draft;
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

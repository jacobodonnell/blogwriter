<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\GenerateUniqueSlugAction;
use App\Enums\Status;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    /**
     * Check if a slug matches the auto-generated placeholder pattern (e.g. "untitled-a1b2c3d4").
     */
    public static function isPlaceholderSlug(string $slug): bool
    {
        return (bool) preg_match('/^untitled-[a-z0-9]{8}$/', $slug);
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
     * Get the permalink for this article.
     */
    public function permalink(): string
    {
        return route('articles.show', $this->slug);
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
        return $this->status === Status::Published && $this->published_at <= now();
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
     * Check if the article has unsaved draft edits staged for the next full save.
     */
    public function hasDraft(): bool
    {
        return $this->draft_content !== null && $this->draft_content !== $this->content;
    }

    protected static function booted(): void
    {
        self::saving(function ($article): void {
            if (empty($article->slug)) {
                $article->slug = app(GenerateUniqueSlugAction::class)
                    ->handle($article->title, Article::class, $article->id);
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
     * Strip microseconds from published_at for database consistency.
     */
    protected function publishedAt(): Attribute
    {
        return Attribute::make(
            set: function (mixed $value): mixed {
                if ($value !== null) {
                    return \Carbon\Carbon::parse($value)->startOfSecond();
                }

                return $value;
            },
        );
    }

    /**
     * Get the featured image URL — meta URL first, then Photo relationship.
     */
    protected function featuredImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => Arr::get($this->meta ?? [], 'featured_image_url')
                ?? $this->featuredPhoto?->image_url,
        );
    }

    /**
     * Get the featured image caption — custom meta caption, or Photo's native caption if toggled.
     */
    protected function featuredImageCaption(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $meta = $this->meta ?? [];

                if (! empty($meta['featured_image_caption'])) {
                    return $meta['featured_image_caption'];
                }

                if (! empty($meta['use_photo_caption']) && $this->photo_id) {
                    return $this->featuredPhoto?->caption;
                }

                return null;
            },
        );
    }

    /**
     * Get the featured image alt text — meta override, then Photo alt_text, then article title.
     */
    protected function featuredImageAlt(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $meta = $this->meta ?? [];

                if (! empty($meta['featured_image_alt'])) {
                    return $meta['featured_image_alt'];
                }

                if ($this->photo_id && $this->featuredPhoto?->alt_text) {
                    return $this->featuredPhoto->alt_text;
                }

                return $this->title;
            },
        );
    }

    /**
     * Scope for published articles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[Scope]
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
    #[Scope]
    protected function draft($query)
    {
        return $query->where('status', Status::Draft);
    }

    /**
     * Get the HTML content rendered from markdown with XSS protection.
     */
    protected function contentHtml(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Markdown::render($this->content ?? ''),
        );
    }

    /**
     * Get the excerpt - either summary or first 500 chars of content.
     */
    protected function excerpt(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (! empty($this->summary)) {
                    return $this->summary;
                }

                return Str::limit(Markdown::toPlainText($this->content ?? ''), 500, '');
            },
        );
    }

    /**
     * Get the estimated reading time in minutes (200 wpm).
     */
    protected function readingTime(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $wordCount = str_word_count(strip_tags($this->content ?? ''));

                return max(1, (int) ceil($wordCount / 200));
            },
        );
    }

    /**
     * Get the meta title or fallback to article title.
     */
    protected function metaTitle(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->meta['meta_title'] ?? $this->title,
        );
    }

    /**
     * Get the meta description or fallback to excerpt.
     */
    protected function metaDescription(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->meta['meta_description'] ?? $this->excerpt,
        );
    }

    /**
     * Get the Open Graph image or fallback to featured image.
     */
    protected function ogImage(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->meta['og_image']
                ?? $this->featured_image_url
                ?? placeholder_image_url(),
        );
    }
}

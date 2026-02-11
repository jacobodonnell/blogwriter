<?php

namespace App\Models;

use App\Enums\Status;
use App\Services\ImageProcessingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
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
        'featured_image_width',
        'featured_image_height',
        'featured_image_file_size',
        'featured_image_mime_type',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'past_slugs' => 'array',
            'published_at' => 'datetime',
            'last_edited_at' => 'datetime',
            'status' => Status::class,
            'featured_image_width' => 'integer',
            'featured_image_height' => 'integer',
            'featured_image_file_size' => 'integer',
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
            // When status changes TO 'published' and published_at is null, set it to now
            $originalStatus = $article->getOriginal('status');
            $newStatus = $article->status;

            if ($article->isDirty('status') && ($newStatus === Status::Published && is_null($article->published_at))) {
                $article->published_at = now()->startOfSecond();
            }

            // When saving an article that was already published before (has original published_at),
            // track the edit time. This handles both:
            // 1. Editing an already-published article (status stays published)
            // 2. Re-publishing a previously-published article (status changes from draft to published)
            // Only set last_edited_at if:
            // 1. Status is 'published'
            // 2. The article was already published before this save (has original published_at)
            // 3. last_edited_at hasn't been manually set already
            if ($newStatus === Status::Published
                && ! is_null($article->getOriginal('published_at'))
                && is_null($article->last_edited_at)) {
                $article->last_edited_at = now()->startOfSecond();
            }

            // Clear metadata when removing image
            if ($article->isDirty('featured_image') && is_null($article->featured_image)) {
                $article->featured_image_width = null;
                $article->featured_image_height = null;
                $article->featured_image_file_size = null;
                $article->featured_image_mime_type = null;
            }
        });

        // Process images AFTER save so article ID is available
        static::saved(function ($article): void {
            if (! $article->featured_image || Str::isUrl($article->featured_image)) {
                return;
            }

            $targetDisk = $article->status === Status::Published ? 'public' : 'private';
            $service = app(ImageProcessingService::class);

            // Process featured image if it's a new file upload (not already processed)
            // Check by seeing if it follows the old storage pattern (articles/featured/*.ext)
            if (str_starts_with($article->featured_image, 'articles/featured/') && ! str_starts_with($article->featured_image, "articles/featured/{$article->id}/")) {
                // Check if this is a newly uploaded file (not already processed)
                if (Storage::disk($targetDisk)->exists($article->featured_image)) {
                    $sourcePath = $article->featured_image;

                    // Only process if the image is valid (not corrupted)
                    if ($service->isValidImage($sourcePath, $targetDisk)) {
                        $metadata = $service->processUpload($article, $sourcePath, $targetDisk);

                        // Update the article with processed image info (without triggering events again)
                        $article->featured_image = $metadata['path'];
                        $article->featured_image_width = $metadata['width'];
                        $article->featured_image_height = $metadata['height'];
                        $article->featured_image_file_size = $metadata['file_size'];
                        $article->featured_image_mime_type = $metadata['mime_type'];
                        $article->saveQuietly();
                    }
                }
            }

            // Handle status change - move images between disks
            if ($article->wasChanged('status')) {
                // Status changed - move all image sizes to appropriate disk
                $oldStatus = $article->getOriginal('status');
                $oldDisk = $oldStatus === Status::Published->value ? 'public' : 'private';
                $service->moveImages($article, $oldDisk, $targetDisk);
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
     * Scope for hidden articles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function hidden($query)
    {
        return $query->where('status', Status::Hidden);
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
        return $query->whereIn('status', [Status::Published, Status::Hidden]);
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
     * Hide the article.
     */
    public function hide(): void
    {
        $this->status = Status::Hidden;
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

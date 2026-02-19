<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Photo extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'articles',
    ];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'published_at' => 'immutable_datetime',
            'taken_at' => 'immutable_datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Register MediaLibrary collections for photos.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif']);
    }

    /**
     * Register MediaLibrary conversions for image optimization.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(300)->height(300)
            ->format('webp')->quality(80)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(768)->height(768)
            ->format('webp')->quality(85)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1536)->height(1536)
            ->format('webp')->quality(85)
            ->nonQueued();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all articles using this photo as featured image.
     *
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'photo_id');
    }

    /**
     * Get the photo's image URL from MediaLibrary.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $media = $this->getFirstMedia('image');

                if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
                    return null;
                }

                if ($media->disk === 'private') {
                    return route('admin.media.show', ['media' => $media->id, 'conversion' => 'large']);
                }

                return $media->getUrl('large');
            }
        );
    }

    /**
     * Get the photo's thumbnail URL from MediaLibrary.
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $media = $this->getFirstMedia('image');

                if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
                    return null;
                }

                if ($media->disk === 'private') {
                    return route('admin.media.show', ['media' => $media->id, 'conversion' => 'thumbnail']);
                }

                return $media->getUrl('thumbnail');
            }
        );
    }

    /**
     * Scope for published photos.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function published($query)
    {
        return $query->where('status', Status::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft photos.
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
     * Check if photo is visible to public.
     */
    public function isPublic(): bool
    {
        return $this->status === Status::Published
            && $this->published_at !== null
            && $this->published_at <= now();
    }
}

<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Photo extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'filename',
        'slug',
        'caption',
        'alt_text',
        'status',
        'published_at',
        'taken_at',
        'meta',
    ];

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
     * Get all articles using this photo as featured image.
     *
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'photo_id');
    }

    /**
     * Get the photo's image URL (external or uploaded).
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->meta['external_url'] ?? $this->getFirstMedia('image')?->getUrl('large')
        );
    }

    /**
     * Check if photo uses external URL instead of uploaded file.
     */
    public function isExternalUrl(): bool
    {
        return isset($this->meta['external_url']);
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
     * Check if photo is visible to public.
     */
    public function isPublic(): bool
    {
        return $this->status === Status::Published
            && $this->published_at !== null
            && $this->published_at <= now();
    }
}

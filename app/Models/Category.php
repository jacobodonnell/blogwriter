<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($category): void {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * @return BelongsToMany<Article, $this>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    /**
     * Get the count of published articles.
     */
    public function getArticleCountAttribute(): int
    {
        return $this->articles()->published()->count();
    }

    /**
     * Get the permalink for this category.
     */
    public function permalink(): string
    {
        return route('category.show', $this->slug);
    }
}

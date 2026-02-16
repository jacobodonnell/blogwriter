<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

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
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Walk up the parent chain and return ancestors (nearest first).
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse()->values();
    }

    /**
     * Recursively collect all descendant category IDs.
     *
     * @return array<int>
     */
    public function descendantIds(): array
    {
        $ids = [];

        foreach ($this->children()->with('children')->get() as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->descendantIds());
        }

        return $ids;
    }

    /**
     * Get the count of published articles including descendants.
     */
    public function getArticleCountAttribute(): int
    {
        $ids = array_merge([$this->id], $this->descendantIds());

        return Article::query()
            ->published()
            ->whereIn('category_id', $ids)
            ->count();
    }

    /**
     * Check if this is a root category (no parent).
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Get the depth of this category in the hierarchy (0 = root).
     */
    public function depth(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Get the permalink for this category.
     */
    public function permalink(): string
    {
        return route('category.show', $this->slug);
    }
}

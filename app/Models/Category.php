<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function ($category): void {
            if (empty($category->slug)) {
                $category->slug = app(\App\Actions\GenerateUniqueSlugAction::class)
                    ->handle($category->name, Category::class, $category->id);
            }
        });
    }

    /**
     * Scope to root categories with eager-loaded children, ordered by name.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    #[Scope]
    protected function tree(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->with('children')->orderBy('name');
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
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Walk up the parent chain and return ancestors (root first) using a recursive CTE.
     */
    public function ancestors(): Collection
    {
        if (! $this->parent_id) {
            return collect();
        }

        $rows = DB::select(<<<'SQL'
            WITH RECURSIVE ancestor_tree AS (
                SELECT *, 0 AS depth FROM categories WHERE id = ?
                UNION ALL
                SELECT c.*, a.depth + 1 FROM categories c
                INNER JOIN ancestor_tree a ON c.id = a.parent_id
            )
            SELECT * FROM ancestor_tree ORDER BY depth DESC
        SQL, [$this->parent_id]);

        return self::hydrate($rows);
    }

    /**
     * Collect all descendant category IDs using a recursive CTE.
     *
     * @return array<int>
     */
    public function descendantIds(): array
    {
        $rows = DB::select(<<<'SQL'
            WITH RECURSIVE descendant_tree AS (
                SELECT id FROM categories WHERE parent_id = ?
                UNION ALL
                SELECT c.id FROM categories c
                INNER JOIN descendant_tree d ON c.parent_id = d.id
            )
            SELECT id FROM descendant_tree
        SQL, [$this->id]);

        return array_column($rows, 'id');
    }

    /**
     * Get all categories as a flat collection with depth for use in select dropdowns.
     *
     * @return Collection<int, Category>
     */
    public static function flatTree(): Collection
    {
        $rows = DB::select(<<<'SQL'
            WITH RECURSIVE category_tree AS (
                SELECT *, 0 AS depth, name AS sort_path FROM categories WHERE parent_id IS NULL
                UNION ALL
                SELECT c.*, ct.depth + 1, ct.sort_path || '/' || c.name FROM categories c
                INNER JOIN category_tree ct ON c.parent_id = ct.id
            )
            SELECT * FROM category_tree ORDER BY sort_path
        SQL);

        $categories = self::hydrate($rows);

        // Attach depth as an attribute for display purposes
        foreach ($categories as $i => $category) {
            $category->depth = $rows[$i]->depth;
        }

        return $categories;
    }

    /**
     * Get the permalink for this category using full ancestor path.
     */
    public function permalink(): string
    {
        $slugs = $this->ancestors()->pluck('slug')->push($this->slug)->all();

        return route('categories.show', implode('/', $slugs));
    }
}

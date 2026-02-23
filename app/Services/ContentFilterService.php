<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ContentFilterService
{
    /**
     * Filter and paginate articles with auth-based scoping, search, status, category, and sort.
     *
     * @param  array<int>|null  $categoryIds
     * @param  array<string, mixed>  $options  Override defaults: perPage, pageName, sort, eagerLoad, adminMode
     */
    public function filterArticles(Request $request, ?array $categoryIds = null, array $options = []): LengthAwarePaginator
    {
        $adminMode = $options['adminMode'] ?? false;

        $query = $adminMode
            ? Article::query()
            : (auth()->check() ? Article::query() : Article::published());

        $this->applyCategory($query, $request, $categoryIds);
        $this->applySearch($query, $request, ['title', 'slug']);
        $this->applyStatus($query, $request, $adminMode || auth()->check(), $adminMode ? null : 'published');

        $eagerLoad = $options['eagerLoad'] ?? ['category', 'featuredPhoto'];
        $query->with($eagerLoad);

        if ($adminMode) {
            $this->applyAdminSort($query, $request, $options['allowedSorts'] ?? ['title', 'status', 'published_at', 'created_at', 'updated_at'], $options['defaultSort'] ?? 'updated_at');
        } else {
            $this->applySortOrder($query, $request, 'title');
        }

        $perPage = $this->resolvePerPage($request, $options['allowedPerPage'] ?? null, $options['perPage'] ?? 10);
        $pageName = $options['pageName'] ?? ($adminMode ? 'page' : 'articles_page');

        return $query->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    /**
     * Filter and paginate photos with auth-based scoping, search, status, category, and sort.
     *
     * @param  array<int>|null  $categoryIds
     * @param  array<string, mixed>  $options  Override defaults: perPage, pageName, sort, eagerLoad, adminMode
     */
    public function filterPhotos(Request $request, ?array $categoryIds = null, array $options = []): LengthAwarePaginator
    {
        $adminMode = $options['adminMode'] ?? false;

        $query = $adminMode
            ? Photo::query()
            : (auth()->check() ? Photo::query() : Photo::published());

        $this->applyCategory($query, $request, $categoryIds);
        $this->applySearch($query, $request, ['alt_text', 'slug', 'caption']);
        $this->applyStatus($query, $request, $adminMode || auth()->check(), $adminMode ? null : 'published');

        if ($adminMode) {
            $query->orderBy('created_at', 'desc');
        } else {
            $this->applySortOrder($query, $request, 'alt_text');
        }

        $perPage = $this->resolvePerPage($request, $options['allowedPerPage'] ?? null, $options['perPage'] ?? 12);
        $pageName = $options['pageName'] ?? ($adminMode ? 'page' : 'photos_page');

        return $query->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    /**
     * Count articles, optionally scoped to a category, with auth-based visibility.
     */
    public function countArticles(?Category $category = null): int
    {
        $query = $category instanceof Category ? $category->articles() : Article::query();

        return auth()->check()
            ? $query->count()
            : $query->published()->count();
    }

    /**
     * Count photos, optionally scoped to a category, with auth-based visibility.
     */
    public function countPhotos(?Category $category = null): int
    {
        $query = $category instanceof Category ? $category->photos() : Photo::query();

        return auth()->check()
            ? $query->count()
            : $query->published()->count();
    }

    /**
     * Apply category filter from request or explicit IDs.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int>|null  $categoryIds
     */
    private function applyCategory(Builder $query, Request $request, ?array $categoryIds): void
    {
        if ($categoryIds !== null) {
            $query->whereIn('category_id', $categoryIds);
        } elseif ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }
    }

    /**
     * Apply search filter across specified columns.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string>  $columns
     */
    private function applySearch(Builder $query, Request $request, array $columns): void
    {
        if (! $request->filled('search')) {
            return;
        }

        $search = $request->input('search');
        $query->where(function ($q) use ($search, $columns): void {
            foreach ($columns as $i => $column) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->{$method}($column, 'like', sprintf('%%%s%%', $search));
            }
        });
    }

    /**
     * Apply status filter when allowed.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function applyStatus(Builder $query, Request $request, bool $allowed, ?string $default = null): void
    {
        if (! $allowed) {
            return;
        }

        $status = $request->input('status') ?: $default;

        if ($status === null || $status === 'all') {
            return;
        }

        $query->where('status', Status::from($status));
    }

    /**
     * Apply sort order for public-facing pages.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function applySortOrder(Builder $query, Request $request, string $titleColumn): void
    {
        match ($request->query('sort')) {
            'oldest' => $query->orderBy('published_at', 'asc'),
            'title_asc' => $query->orderBy($titleColumn, 'asc'),
            'title_desc' => $query->orderBy($titleColumn, 'desc'),
            default => $query->orderBy('published_at', 'desc'),
        };
    }

    /**
     * Apply sort for admin table views (column + direction).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string>  $allowedSorts
     */
    private function applyAdminSort(Builder $query, Request $request, array $allowedSorts, string $defaultSort): void
    {
        $sort = in_array($request->input('sort'), $allowedSorts)
            ? $request->input('sort')
            : $defaultSort;
        $direction = in_array($request->input('direction'), ['asc', 'desc'])
            ? $request->input('direction')
            : 'desc';

        $query->orderBy($sort, $direction);
    }

    /**
     * Resolve per-page value from request, constrained to allowed values.
     *
     * @param  array<int>|null  $allowed
     */
    private function resolvePerPage(Request $request, ?array $allowed, int $default): int
    {
        if ($allowed === null) {
            return $default;
        }

        $value = (int) $request->input('perPage');

        return in_array($value, $allowed) ? $value : $default;
    }
}

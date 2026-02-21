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
     */
    public function filterArticles(Request $request, ?array $categoryIds = null): LengthAwarePaginator
    {
        $isAuth = auth()->check();

        $query = $isAuth
            ? Article::query()
            : Article::published();

        if ($categoryIds !== null) {
            $query->whereIn('category_id', $categoryIds);
        } elseif ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($search = $request->query('search')) {
            $query->where('title', 'like', sprintf('%%%s%%', $search));
        }

        $status = $isAuth && $request->filled('status')
            ? Status::from($request->input('status'))
            : null;

        if ($status) {
            $query->where('status', $status);
        }

        $this->applySortOrder($query, $request, 'title');

        return $query->with(['category', 'featuredPhoto'])
            ->paginate(10, ['*'], 'articles_page')
            ->withQueryString();
    }

    /**
     * Filter and paginate photos with auth-based scoping, search, status, category, and sort.
     *
     * @param  array<int>|null  $categoryIds
     */
    public function filterPhotos(Request $request, ?array $categoryIds = null): LengthAwarePaginator
    {
        $isAuth = auth()->check();

        $query = $isAuth
            ? Photo::query()
            : Photo::published();

        if ($categoryIds !== null) {
            $query->whereIn('category_id', $categoryIds);
        } elseif ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('alt_text', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('caption', 'like', sprintf('%%%s%%', $search));
            });
        }

        $status = $isAuth && $request->filled('status')
            ? Status::from($request->input('status'))
            : null;

        if ($status) {
            $query->where('status', $status);
        }

        $this->applySortOrder($query, $request, 'alt_text');

        return $query->paginate(12, ['*'], 'photos_page')
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
     * Apply sort order to a query based on the request's `sort` parameter.
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
}

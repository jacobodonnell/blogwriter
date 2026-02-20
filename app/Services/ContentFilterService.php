<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ContentFilterService
{
    /**
     * Filter and paginate articles with auth-based scoping, search, status, and category filters.
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

        return $query->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(10, ['*'], 'articles_page')
            ->withQueryString();
    }

    /**
     * Filter and paginate photos with auth-based scoping, search, status, and category filters.
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

        return $query->orderBy('published_at', 'desc')
            ->paginate(12, ['*'], 'photos_page')
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
}

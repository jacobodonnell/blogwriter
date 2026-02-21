<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ContentFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class CategoryExploreController extends Controller
{
    public function __construct(
        private readonly ContentFilterService $contentFilter,
    ) {}

    /**
     * Display all content across all categories (root explore view).
     */
    public function index(Request $request): View
    {
        $type = $this->resolveType($request);

        $articles = new LengthAwarePaginator([], 0, 10);
        $photos = new LengthAwarePaginator([], 0, 12);

        if ($type === 'all' || $type === 'articles') {
            $articles = $this->contentFilter->filterArticles($request);
        }

        if ($type === 'all' || $type === 'photos') {
            $photos = $this->contentFilter->filterPhotos($request);
        }

        $viewData = [
            'category' => null,
            'categoryPath' => null,
            'selectedCategory' => null,
            'children' => Category::tree()->get(),
            'categories' => Category::flatTree(),
            'articles' => $articles,
            'photos' => $photos,
            'currentType' => $type,
            'articleCount' => $this->contentFilter->countArticles(),
            'photoCount' => $this->contentFilter->countPhotos(),
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.categories._explore-content', $viewData);
        }

        return view('admin.categories.explore', $viewData);
    }

    /**
     * Display content within a specific category (drill-down explore view).
     */
    public function show(Request $request, string $path): View
    {
        $category = $this->resolveSlugPath($path);
        $categoryIds = array_merge([$category->id], $category->descendantIds());

        $type = $this->resolveType($request);

        $articles = new LengthAwarePaginator([], 0, 10);
        $photos = new LengthAwarePaginator([], 0, 12);

        if ($type === 'all' || $type === 'articles') {
            $articles = $this->contentFilter->filterArticles($request, $categoryIds);
        }

        if ($type === 'all' || $type === 'photos') {
            $photos = $this->contentFilter->filterPhotos($request, $categoryIds);
        }

        $categoryPath = $category->ancestors()->pluck('name')->push($category->name)->implode('/');

        $viewData = [
            'category' => $category,
            'categoryPath' => $categoryPath,
            'selectedCategory' => $category->slug,
            'categories' => Category::flatTree(),
            'articles' => $articles,
            'photos' => $photos,
            'children' => $category->children()->orderBy('name')->get(),
            'currentType' => $type,
            'articleCount' => $this->contentFilter->countArticles($category),
            'photoCount' => $this->contentFilter->countPhotos($category),
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.categories._explore-content', $viewData);
        }

        return view('admin.categories.explore', $viewData);
    }

    private function resolveType(Request $request): string
    {
        $type = $request->query('type', 'all');

        if (! in_array($type, ['all', 'articles', 'photos'])) {
            return 'all';
        }

        return $type;
    }

    private function resolveSlugPath(string $path): Category
    {
        $segments = explode('/', $path);
        $parentId = null;
        $category = null;

        foreach ($segments as $segment) {
            $category = Category::where('slug', $segment)
                ->where('parent_id', $parentId)
                ->firstOrFail();

            $parentId = $category->id;
        }

        return $category;
    }
}

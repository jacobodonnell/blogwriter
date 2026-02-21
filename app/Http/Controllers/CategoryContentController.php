<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ContentFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class CategoryContentController extends Controller
{
    public function __construct(
        private readonly ContentFilterService $contentFilter,
    ) {}

    /**
     * Display articles and photos by category (including subcategories).
     */
    public function index(Request $request, string $path): View
    {
        $segments = explode('/', $path);
        $parentId = null;

        foreach ($segments as $segment) {
            $category = Category::where('slug', $segment)
                ->where('parent_id', $parentId)
                ->firstOrFail();

            $parentId = $category->id;
        }

        $categoryIds = array_merge([$category->id], $category->descendantIds());

        $type = $request->query('type', 'all');

        if (! in_array($type, ['all', 'articles', 'photos'])) {
            $type = 'all';
        }

        $articles = new LengthAwarePaginator([], 0, 10);
        $photos = new LengthAwarePaginator([], 0, 12);

        if ($type === 'all' || $type === 'articles') {
            $articles = $this->contentFilter->filterArticles($request, $categoryIds);
        }

        if ($type === 'all' || $type === 'photos') {
            $photos = $this->contentFilter->filterPhotos($request, $categoryIds);
        }

        return view('public.category', [
            'category' => $category,
            'categories' => Category::tree()->get(),
            'articles' => $articles,
            'photos' => $photos,
            'children' => $category->children()->orderBy('name')->get(),
            'currentType' => $type,
            'articleCount' => $this->contentFilter->countArticles($category),
            'photoCount' => $this->contentFilter->countPhotos($category),
        ]);
    }
}

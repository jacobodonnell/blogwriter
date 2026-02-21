<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ContentFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly ContentFilterService $contentFilter,
    ) {}

    /**
     * Display all content across all categories (root feed).
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'all');

        if (! in_array($type, ['all', 'articles', 'photos'])) {
            $type = 'all';
        }

        $articles = new LengthAwarePaginator([], 0, 10);
        $photos = new LengthAwarePaginator([], 0, 12);

        if ($type === 'all' || $type === 'articles') {
            $articles = $this->contentFilter->filterArticles($request);
        }

        if ($type === 'all' || $type === 'photos') {
            $photos = $this->contentFilter->filterPhotos($request);
        }

        $categoryTree = Category::tree()->get();

        return view('public.categories', [
            'children' => $categoryTree,
            'categories' => $categoryTree,
            'articles' => $articles,
            'photos' => $photos,
            'currentType' => $type,
            'articleCount' => $this->contentFilter->countArticles(),
            'photoCount' => $this->contentFilter->countPhotos(),
        ]);
    }
}

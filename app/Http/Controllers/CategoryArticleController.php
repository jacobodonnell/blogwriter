<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryArticleController extends Controller
{
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

        $articles = null;
        $photos = null;

        if ($type === 'all' || $type === 'articles') {
            $articleQuery = auth()->check()
                ? Article::whereIn('category_id', $categoryIds)
                : Article::published()->whereIn('category_id', $categoryIds);

            $articles = $articleQuery->with('category')
                ->orderBy('published_at', 'desc')
                ->paginate(10, ['*'], 'articles_page')
                ->withQueryString();
        }

        if ($type === 'all' || $type === 'photos') {
            $photoQuery = auth()->check()
                ? Photo::whereIn('category_id', $categoryIds)
                : Photo::published()->whereIn('category_id', $categoryIds);

            $photos = $photoQuery->orderBy('published_at', 'desc')
                ->paginate(12, ['*'], 'photos_page')
                ->withQueryString();
        }

        $children = $category->children()->orderBy('name')->get();

        $viewData = [
            'category' => $category,
            'articles' => $articles,
            'photos' => $photos,
            'children' => $children,
            'currentType' => $type,
        ];

        return view('public.category', $viewData);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class CategoryArticleController extends Controller
{
    /**
     * Display articles by category (including subcategory articles).
     */
    public function index(string $path): View
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

        $articles = Article::published()
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        $children = $category->children()->orderBy('name')->get();

        return view('public.category', [
            'category' => $category,
            'articles' => $articles,
            'children' => $children,
        ]);
    }
}

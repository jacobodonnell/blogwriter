<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class CategoryArticleController extends Controller
{
    /**
     * Display articles by category (including subcategory articles).
     */
    public function index(string $slug): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $categoryIds = array_merge([$category->id], $category->descendantIds());

        $articles = Article::published()
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('public.category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}

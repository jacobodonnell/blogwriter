<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;

class CategoryArticleController extends Controller
{
    /**
     * Display articles by category.
     */
    public function index(string $slug): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $articles = Article::published()
            ->whereHas('categories', function ($query) use ($category): void {
                $query->where('categories.id', $category->id);
            })
            ->with('categories')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('public.category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}

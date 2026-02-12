<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display the homepage with recent articles.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $articles = Article::published()
            ->with('categories')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('public.index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Display a single article.
     */
    public function show(string $slug): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->with('categories')
            ->firstOrFail();

        return view('public.article', [
            'article' => $article,
        ]);
    }
}

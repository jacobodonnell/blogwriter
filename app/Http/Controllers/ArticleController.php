<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display the articles listing page.
     */
    public function index(): View
    {
        $articles = Article::published()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('public.articles', [
            'articles' => $articles,
            'subtitle' => setting('page_articles_subtitle', ''),
        ]);
    }

    /**
     * Display a single article.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->with('category')
            ->first();

        if ($article) {
            return view('public.article', [
                'article' => $article,
            ]);
        }

        // Check past slugs for 301 redirect
        $article = Article::published()
            ->whereJsonContains('past_slugs', $slug)
            ->first();

        if ($article) {
            return redirect()->route('article.show', $article->slug, 301);
        }

        abort(404);
    }
}

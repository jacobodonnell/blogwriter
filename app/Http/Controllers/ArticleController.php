<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Services\ContentFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ArticleController extends Controller
{
    public function __construct(
        private readonly ContentFilterService $contentFilter,
    ) {}

    /**
     * Display the articles listing page.
     */
    public function index(Request $request): View
    {
        $articles = $this->contentFilter->filterArticles($request, options: [
            'eagerLoad' => ['category'],
        ]);

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('public.articles', [
            'articles' => $articles,
            'subtitle' => setting('page_articles_subtitle', ''),
            'categories' => $categories,
        ]);
    }

    /**
     * Display a single article.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $query = auth()->check() ? Article::query() : Article::published();
        $article = $query->where('slug', $slug)
            ->with('category')
            ->first();

        if ($article) {
            return view('public.article', ['article' => $article]);
        }

        $article = Article::published()
            ->whereJsonContains('past_slugs', $slug)
            ->first();

        if ($article) {
            return redirect()->route('articles.show', $article->slug, 301);
        }

        abort(404);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display the articles listing page.
     */
    public function index(Request $request): View
    {
        $articleQuery = auth()->check()
            ? Article::query()
            : Article::published();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $articleQuery->where(function ($q) use ($search): void {
                $q->where('title', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('slug', 'like', sprintf('%%%s%%', $search));
            });
        }

        if ($request->filled('category')) {
            $articleQuery->where('category_id', $request->input('category'));
        }

        if (auth()->check() && $request->filled('status')) {
            $articleQuery->where('status', Status::from($request->input('status')));
        }

        $articles = $articleQuery->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(10)
            ->withQueryString();

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
        // Auth users can view any article, guests only published
        $baseQuery = auth()->check()
            ? Article::query()
            : Article::published();

        $article = $baseQuery->where('slug', $slug)
            ->with('category')
            ->first();

        if ($article) {
            return view('public.article', [
                'article' => $article,
            ]);
        }

        // Check past slugs for 301 redirect
        $redirectQuery = auth()->check()
            ? Article::query()
            : Article::published();

        $article = $redirectQuery->whereJsonContains('past_slugs', $slug)
            ->first();

        if ($article) {
            return redirect()->route('articles.show', $article->slug, 301);
        }

        abort(404);
    }
}

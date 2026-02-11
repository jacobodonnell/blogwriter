<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $query = Article::query()
            ->with('categories')
            ->orderBy('updated_at', 'desc');

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request): void {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $articles = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created article.
     */
    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();

        $article = new Article;
        $article->title = $data['title'];
        $article->slug = $data['slug'];
        $article->content = $data['content'];
        $article->status = $data['status'];
        $article->published_at = $data['published_at'] ?? null;
        $article->meta = $data['meta'] ?? [];

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('articles/featured', 'public');
            $article->featured_image = $path;
        } elseif ($request->filled('featured_image_url')) {
            $article->featured_image = $request->input('featured_image_url');
        }

        if (empty($data['summary'])) {
            $article->summary = Str::limit(strip_tags((string) $data['content']), 255);
        } else {
            $article->summary = $data['summary'];
        }

        $article->save();

        if (! empty($data['categories'])) {
            $article->categories()->attach($data['categories']);
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully.');
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $article->load('categories');
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.edit', [
            'article' => $article,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $data = $request->validated();

        $article->title = $data['title'];
        $article->slug = $data['slug'];
        $article->content = $data['content'];
        $article->status = $data['status'];
        if (array_key_exists('published_at', $data)) {
            $article->published_at = $data['published_at'];
        }
        $article->meta = $data['meta'] ?? [];

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('articles/featured', 'public');
            $article->featured_image = $path;
        } elseif ($request->filled('featured_image_url')) {
            $article->featured_image = $request->input('featured_image_url');
        }

        if (empty($data['summary'])) {
            $article->summary = Str::limit(strip_tags((string) $data['content']), 255);
        } else {
            $article->summary = $data['summary'];
        }

        $article->save();

        $article->categories()->sync($data['categories'] ?? []);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Article $article)
    {
        $article->categories()->detach();
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}

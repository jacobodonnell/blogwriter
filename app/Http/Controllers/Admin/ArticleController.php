<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Actions\Photos\HandleArticlePhotoUploadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        private readonly HandleArticlePhotoUploadAction $handlePhotoUpload,
        private readonly GenerateArticleSummaryAction $generateSummary,
    ) {}

    /**
     * Display a listing of articles.
     */
    public function index(Request $request): View
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
     * Display the full-page preview for an article.
     */
    public function show(Article $article): View
    {
        $article->load('categories');

        return view('admin.articles.preview-fullscreen', [
            'article' => $article,
        ]);
    }

    /**
     * Show the customizer editor for the specified article.
     */
    public function edit(Article $article): View
    {
        $article->load('categories');
        $categories = Category::orderBy('name')->get();
        $photos = Photo::published()->latest()->limit(50)->get();

        return view('admin.articles.customizer', [
            'article' => $article,
            'categories' => $categories,
            'photos' => $photos,
            'isNew' => false,
        ]);
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $data = $request->validated();
        $meta = $data['meta'] ?? [];

        // Handle featured image removal
        if ($request->boolean('remove_featured_image')) {
            $data['photo_id'] = null;
            unset($meta['featured_image_url']);
        } elseif ($request->hasFile('featured_image_file')) {
            $result = $this->handlePhotoUpload->handle(
                $request->file('featured_image_file'),
                array_merge($data, $request->only('featured_image_alt', 'featured_image_caption')),
                $article->id,
            );
            if ($result instanceof RedirectResponse) {
                return $result;
            }

            $data['photo_id'] = $result;
            unset($meta['featured_image_url']);
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            $meta['featured_image_url'] = $request->featured_image;
            $data['photo_id'] = null;
        } elseif ($request->filled('photo_id')) {
            unset($meta['featured_image_url']);
        } else {
            $data['photo_id'] = $article->photo_id;
            // Preserve existing meta featured_image_url if present
            $existingMeta = $article->meta ?? [];
            if (isset($existingMeta['featured_image_url']) && ! isset($meta['featured_image_url'])) {
                $meta['featured_image_url'] = $existingMeta['featured_image_url'];
            }
        }

        $article->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'] ?? $article->content,
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content'] ?? $article->content),
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? $article->published_at,
            'meta' => $meta,
            'photo_id' => $data['photo_id'],
        ]);

        $article->categories()->sync($data['categories'] ?? []);

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->categories()->detach();
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}

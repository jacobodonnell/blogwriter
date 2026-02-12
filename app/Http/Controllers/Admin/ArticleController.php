<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Actions\Photos\CreatePhotoFromUrlAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly CreatePhotoFromUrlAction $createPhotoFromUrl,
        private readonly CreatePhotoFromUploadAction $createPhotoFromUpload,
        private readonly GenerateArticleSummaryAction $generateSummary,
    ) {}

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

        // Handle featured image
        $photoId = match (true) {
            $request->hasFile('featured_image_file') => $this->handlePhotoUpload($request->file('featured_image_file'), $data),
            $request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL) => $this->handlePhotoUrl($request->featured_image, $data),
            $request->filled('photo_id') => $data['photo_id'],
            default => null,
        };

        if ($photoId instanceof \Illuminate\Http\RedirectResponse) {
            return $photoId;
        }

        $article = Article::create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content']),
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
            'meta' => $data['meta'] ?? [],
            'photo_id' => $photoId,
        ]);

        if (! empty($data['categories'])) {
            $article->categories()->attach($data['categories']);
        }

        return redirect()->route('admin.articles.edit', $article)
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

        // Handle featured image removal
        if ($request->boolean('remove_featured_image')) {
            $data['photo_id'] = null;
        }

        // Handle featured image
        if (! array_key_exists('photo_id', $data)) {
            $photoId = match (true) {
                $request->hasFile('featured_image_file') => $this->handlePhotoUpload($request->file('featured_image_file'), $data, $article->id),
                $request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL) => $this->handlePhotoUrl($request->featured_image, $data, $article->id),
                $request->filled('photo_id') => $data['photo_id'],
                default => $article->photo_id,
            };

            if ($photoId instanceof \Illuminate\Http\RedirectResponse) {
                return $photoId;
            }

            $data['photo_id'] = $photoId;
        }

        $article->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content']),
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? $article->published_at,
            'meta' => $data['meta'] ?? [],
            'photo_id' => $data['photo_id'],
        ]);

        $article->categories()->sync($data['categories'] ?? []);

        return redirect()->route('admin.articles.edit', $article)
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

    /**
     * Handle photo upload and return photo ID or error response.
     */
    private function handlePhotoUpload($file, array $data, ?int $articleId = null): int|\Illuminate\Http\RedirectResponse
    {
        try {
            $photo = $this->createPhotoFromUpload->handle($file, [
                'slug' => $data['slug'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'alt_text' => $data['title'] ?? 'Featured image',
                'status' => $data['status'] ?? 'draft',
            ]);

            return $photo->id;
        } catch (\Exception $e) {
            \Log::error('Failed to create photo from upload', [
                'article_id' => $articleId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['featured_image_file' => 'Failed to upload image. Please try again.']);
        }
    }

    /**
     * Handle photo from URL and return photo ID or error response.
     */
    private function handlePhotoUrl(string $url, array $data, ?int $articleId = null): int|\Illuminate\Http\RedirectResponse
    {
        try {
            $photo = $this->createPhotoFromUrl->handle($url, [
                'slug' => $data['slug'] ?? basename(parse_url($url, PHP_URL_PATH)),
                'alt_text' => $data['title'] ?? 'Featured image',
                'status' => $data['status'] ?? 'draft',
            ]);

            return $photo->id;
        } catch (\Exception $e) {
            \Log::error('Failed to create photo from URL', [
                'article_id' => $articleId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['featured_image' => 'Failed to process external image URL. Please try again.']);
        }
    }
}

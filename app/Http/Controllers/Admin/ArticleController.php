<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function __construct(
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
     * Create a blank draft article and redirect to customizer.
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => 'Untitled Article',
            'slug' => 'untitled-'.Str::random(8),
            'content' => '',
            'summary' => '',
            'status' => Status::Draft,
        ]);

        return redirect()->route('admin.articles.edit', $article);
    }

    /**
     * Store a newly created article.
     */
    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();

        // Handle featured image
        $photoId = null;
        $meta = $data['meta'] ?? [];

        if ($request->hasFile('featured_image_file')) {
            $photoId = $this->handlePhotoUpload($request->file('featured_image_file'), $data);
            if ($photoId instanceof \Illuminate\Http\RedirectResponse) {
                return $photoId;
            }
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            $meta['featured_image_url'] = $request->featured_image;
        } elseif ($request->filled('photo_id')) {
            $photoId = $data['photo_id'];
        }

        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content']),
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? null,
            'meta' => $meta,
            'photo_id' => $photoId,
        ]);

        if (! empty($data['categories'])) {
            $article->categories()->attach($data['categories']);
        }

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }

    /**
     * Display the full-page preview for an article.
     */
    public function show(Article $article): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $article->load('categories');

        return view('admin.articles.preview-fullscreen', [
            'article' => $article,
        ]);
    }

    /**
     * Show the customizer editor for the specified article.
     */
    public function edit(Article $article): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $article->load('categories');
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.customizer', [
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
        $meta = $data['meta'] ?? [];

        // Handle featured image removal
        if ($request->boolean('remove_featured_image')) {
            $data['photo_id'] = null;
            unset($meta['featured_image_url']);
        } elseif ($request->hasFile('featured_image_file')) {
            $photoId = $this->handlePhotoUpload($request->file('featured_image_file'), $data, $article->id);
            if ($photoId instanceof \Illuminate\Http\RedirectResponse) {
                return $photoId;
            }

            $data['photo_id'] = $photoId;
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
            'content' => $data['content'],
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content']),
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? $article->published_at,
            'meta' => $meta,
            'photo_id' => $data['photo_id'],
        ]);

        $article->categories()->sync($data['categories'] ?? []);

        if ($request->header('X-Alpine-Request')) {
            $article->refresh()->load('categories');

            return view('admin.articles.preview', ['article' => $article]);
        }

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
    private function handlePhotoUpload(\Illuminate\Http\UploadedFile $file, array $data, ?int $articleId = null): int|\Illuminate\Http\RedirectResponse
    {
        try {
            $photo = $this->createPhotoFromUpload->handle($file, [
                'slug' => $data['slug'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'alt_text' => $data['title'] ?? 'Featured image',
                'status' => 'published',
            ]);

            return $photo->id;
        } catch (\Exception $exception) {
            \Log::error('Failed to create photo from upload', [
                'article_id' => $articleId,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['featured_image_file' => 'Failed to upload image. Please try again.']);
        }
    }
}

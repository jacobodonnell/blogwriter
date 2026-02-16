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
     * Allowed sort columns for the articles index.
     *
     * @var array<string>
     */
    private const ALLOWED_SORTS = ['title', 'status', 'published_at', 'created_at', 'updated_at'];

    /**
     * Allowed per-page options for the articles index.
     *
     * @var array<int>
     */
    private const ALLOWED_PER_PAGE = [10, 20, 50, 100];

    /**
     * Display a listing of articles.
     */
    public function index(Request $request): View
    {
        $currentSort = in_array($request->input('sort'), self::ALLOWED_SORTS)
            ? $request->input('sort')
            : 'updated_at';
        $currentDirection = in_array($request->input('direction'), ['asc', 'desc'])
            ? $request->input('direction')
            : 'desc';

        $query = Article::query()
            ->with(['category', 'featuredPhoto.media'])
            ->orderBy($currentSort, $currentDirection);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('slug', 'like', sprintf('%%%s%%', $search));
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array((int) $request->input('perPage'), self::ALLOWED_PER_PAGE)
            ? (int) $request->input('perPage')
            : 20;

        $articles = $query->paginate($perPage)->withQueryString();
        $categories = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $viewData = [
            'articles' => $articles,
            'categories' => $categories,
            'currentSort' => $currentSort,
            'currentDirection' => $currentDirection,
            'perPage' => $perPage,
        ];

        if ($request->header('X-Alpine-Target')) {
            return view('admin.articles._table', $viewData);
        }

        return view('admin.articles.index', $viewData);
    }

    /**
     * Display the full-page preview for an article.
     */
    public function show(Article $article): View
    {
        $article->load('category');

        return view('admin.articles.preview-fullscreen', [
            'article' => $article,
        ]);
    }

    /**
     * Show the customizer editor for the specified article.
     */
    public function edit(Article $article): View
    {
        $article->load('category');
        $categories = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
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
            'category_id' => $data['category_id'] ?? null,
        ]);

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}

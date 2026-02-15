<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Actions\Photos\HandleArticlePhotoUploadAction;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticlePreviewRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CreateArticleController extends Controller
{
    public function __construct(
        private readonly HandleArticlePhotoUploadAction $handlePhotoUpload,
        private readonly GenerateArticleSummaryAction $generateSummary,
    ) {}

    /**
     * Show the customizer for a new (unsaved) article.
     */
    public function create(): View
    {
        $article = new Article([
            'title' => 'Untitled Article',
            'slug' => 'untitled-'.Str::lower(Str::random(8)),
            'content' => '',
            'summary' => '',
            'status' => Status::Draft,
        ]);
        $article->setRelation('categories', collect());

        return view('admin.articles.customizer', [
            'article' => $article,
            'categories' => Category::orderBy('name')->get(),
            'photos' => Photo::published()->latest()->limit(50)->get(),
            'isNew' => true,
        ]);
    }

    /**
     * Auto-save preview for new articles — session only, no DB write.
     */
    public function preview(UpdateArticlePreviewRequest $request): View
    {
        $data = $request->validated();
        session()->put('draft_article', $data);

        $slug = $data['slug'] ?? 'untitled-'.Str::lower(Str::random(8));
        if (preg_match('/^untitled-[a-z0-9]{8}$/', (string) $slug) && ! empty($data['title'])) {
            $slug = Str::slug($data['title']) ?: $slug;
        }

        $article = new Article([
            'title' => $data['title'] ?? 'Untitled Article',
            'slug' => $slug,
            'content' => $data['content'] ?? '',
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content'] ?? ''),
            'status' => $data['status'] ?? Status::Draft,
            'meta' => $data['meta'] ?? [],
        ]);

        // Handle featured image preview
        if (! empty($data['photo_id'])) {
            $article->photo_id = $data['photo_id'];
            $article->setRelation('featuredPhoto', Photo::find($data['photo_id']));
        }

        if (! empty($data['featured_image'])) {
            $meta = $article->meta ?? [];
            $meta['featured_image_url'] = $data['featured_image'];
            $article->meta = $meta;
        }

        // Build categories relation for preview
        $categoryIds = $data['categories'] ?? [];
        $article->setRelation('categories', $categoryIds ? Category::whereIn('id', $categoryIds)->get() : collect());

        return view('admin.articles.preview', ['article' => $article]);
    }

    /**
     * First explicit save — persist article to database.
     */
    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $photoId = null;
        $meta = $data['meta'] ?? [];

        if ($request->hasFile('featured_image_file')) {
            $result = $this->handlePhotoUpload->handle(
                $request->file('featured_image_file'),
                array_merge($data, $request->only('featured_image_alt', 'featured_image_caption')),
            );
            if ($result instanceof RedirectResponse) {
                return $result;
            }

            $photoId = $result;
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            $meta['featured_image_url'] = $request->featured_image;
        } elseif ($request->filled('photo_id')) {
            $photoId = $data['photo_id'];
        }

        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $data['title'] ?? 'Untitled Article',
            'slug' => $data['slug'] ?? 'untitled-'.Str::lower(Str::random(8)),
            'content' => $data['content'] ?? '',
            'summary' => $this->generateSummary->handle($data['summary'] ?? null, $data['content'] ?? ''),
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
            'meta' => $meta,
            'photo_id' => $photoId,
        ]);

        if (! empty($data['categories'])) {
            $article->categories()->attach($data['categories']);
        }

        session()->forget('draft_article');

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }
}

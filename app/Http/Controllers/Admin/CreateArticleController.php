<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Actions\Photos\HandleArticlePhotoUploadAction;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
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

        return view('admin.articles.customizer', [
            'article' => $article,
            'categories' => Category::query()->with('children')->whereNull('parent_id')->orderBy('name')->get(),
            'photos' => Photo::published()->latest()->limit(50)->get(),
            'isNew' => true,
        ]);
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

        // Caption meta mutual exclusion
        if (! empty($meta['use_photo_caption'])) {
            unset($meta['featured_image_caption']);
        } else {
            unset($meta['use_photo_caption']);
        }

        // Clear caption keys if no featured image at all
        if (empty($photoId) && empty($meta['featured_image_url'])) {
            unset($meta['featured_image_caption'], $meta['use_photo_caption']);
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
            'category_id' => $data['category_id'] ?? null,
        ]);

        session()->forget('draft_article');

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }
}

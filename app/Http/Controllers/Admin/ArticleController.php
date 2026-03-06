<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ApplyArticleFeaturedImageAction;
use App\Actions\CreateCategoryFromArticleAction;
use App\Exceptions\PhotoUploadFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Services\ContentFilterService;
use App\Services\RevisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class ArticleController extends Controller
{
    private const ALLOWED_SORTS = ['title', 'status', 'published_at', 'created_at', 'updated_at'];

    private const ALLOWED_PER_PAGE = [10, 20, 50, 100];

    public function __construct(
        private readonly ApplyArticleFeaturedImageAction $applyFeaturedImage,
        private readonly CreateCategoryFromArticleAction $createCategory,
        private readonly ContentFilterService $contentFilter,
        private readonly RevisionService $revisionService,
    ) {}

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

        $perPage = in_array((int) $request->input('perPage'), self::ALLOWED_PER_PAGE)
            ? (int) $request->input('perPage')
            : 20;

        $articles = $this->contentFilter->filterArticles($request, options: [
            'adminMode' => true,
            'eagerLoad' => ['category', 'featuredPhoto.media'],
            'allowedSorts' => self::ALLOWED_SORTS,
            'defaultSort' => 'updated_at',
            'allowedPerPage' => self::ALLOWED_PER_PAGE,
            'perPage' => 20,
        ]);

        $categories = Category::tree()->get();

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
     * Show the customizer editor for the specified article.
     */
    public function edit(Article $article): View
    {
        $article->load('category', 'revisions');

        if ($article->hasDraft()) {
            $article->applyDraft();

            // Re-resolve category relation if draft changed category_id
            if ($article->category_id !== $article->getOriginal('category_id')) {
                $article->setRelation('category', Category::find($article->category_id));
            }
        }

        $categories = Category::tree()->get();
        $photos = Photo::published()->with('media')->latest()->limit(50)->get();

        return view('admin.articles.customizer', [
            'article' => $article,
            'categories' => $categories,
            'photos' => $photos,
            'isNew' => false,
            'liveContent' => $article->getRawOriginal('content') ?? '',
            'liveTitle' => $article->getRawOriginal('title') ?? '',
            'liveSlug' => $article->getRawOriginal('slug') ?? '',
            'liveSummary' => $article->getRawOriginal('summary') ?? '',
            'liveCategoryId' => $article->getOriginal('category_id'),
            'livePhotoId' => $article->getOriginal('photo_id'),
            'liveExternalFeaturedImgUrl' => $article->getOriginal('external_featured_img_url'),
            'liveMeta' => $article->getOriginal('meta') ?? [],
        ]);
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $data = $request->validated();

        try {
            $imageResult = $this->applyFeaturedImage->handle($request, $data, $article);
        } catch (PhotoUploadFailedException $photoUploadFailedException) {
            Log::error('Failed to upload featured image', ['error' => $photoUploadFailedException->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['featured_image_file' => 'Failed to upload image. Please try again.']);
        }

        $this->createCategory->handle($data);

        $originalTitle = $article->title;
        $originalContent = $article->content ?? '';

        $article->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'] ?? $article->content,
            'draft' => null,
            'content_history' => null,
            'summary' => $data['summary'] ?? null,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? $article->published_at,
            'meta' => $imageResult['meta'],
            'photo_id' => $imageResult['photo_id'],
            'external_featured_img_url' => $imageResult['external_featured_img_url'],
            'category_id' => $data['category_id'] ?? null,
        ]);

        $this->revisionService->createRevisionIfChanged($article, $originalTitle, $originalContent);

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

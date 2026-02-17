<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticlePreviewRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CreateArticlePreviewController extends Controller
{
    public function __construct(
        private readonly GenerateArticleSummaryAction $generateSummary,
    ) {}

    /**
     * Auto-save preview for new articles — session only, no DB write.
     */
    public function __invoke(UpdateArticlePreviewRequest $request): View
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

        // Build category relation for preview
        if (! empty($data['category_id'])) {
            $article->category_id = $data['category_id'];
            $article->setRelation('category', Category::find($data['category_id']));
        }

        return view('admin.articles.preview', ['article' => $article]);
    }
}

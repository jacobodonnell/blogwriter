<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApplyArticleFeaturedImageAction;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CreateArticleController extends Controller
{
    public function __construct(
        private readonly ApplyArticleFeaturedImageAction $applyFeaturedImage,
    ) {}

    /**
     * Show the customizer for a new (unsaved) article.
     */
    public function create(Request $request): View
    {
        $article = new Article([
            'title' => 'Untitled Article',
            'slug' => 'untitled-'.Str::lower(Str::random(8)),
            'content' => '',
            'summary' => '',
            'status' => Status::Draft,
            'category_id' => $request->input('category_id'),
        ]);

        return view('admin.articles.customizer', [
            'article' => $article,
            'categories' => Category::tree()->get(),
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

        $imageResult = $this->applyFeaturedImage->handle($request, $data);
        if ($imageResult instanceof RedirectResponse) {
            return $imageResult;
        }

        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $data['title'] ?? 'Untitled Article',
            'slug' => $data['slug'] ?? 'untitled-'.Str::lower(Str::random(8)),
            'content' => $data['content'] ?? '',
            'summary' => $data['summary'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
            'meta' => $imageResult['meta'],
            'photo_id' => $imageResult['photo_id'],
            'category_id' => $data['category_id'] ?? null,
        ]);

        session()->forget('draft_article');

        return redirect()->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }
}

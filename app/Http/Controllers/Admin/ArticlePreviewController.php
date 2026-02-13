<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateArticleSummaryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticlePreviewRequest;
use App\Models\Article;
use Illuminate\Support\Str;

class ArticlePreviewController extends Controller
{
    public function __construct(
        private readonly GenerateArticleSummaryAction $generateSummary,
    ) {}

    /**
     * Update article for live preview (AJAX auto-save).
     */
    public function update(UpdateArticlePreviewRequest $request, Article $article): \Illuminate\Contracts\View\View
    {
        $data = $request->validated();

        $updateData = [
            'status' => $data['status'],
        ];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['slug']) && $data['slug'] !== '') {
            $newSlug = null;

            // Auto-generate slug from title if slug is a placeholder
            if (preg_match('/^untitled-[a-z0-9]{8}$/', $data['slug']) && isset($data['title']) && $data['title'] !== '') {
                $newSlug = Str::slug($data['title']);
            } else {
                $newSlug = $data['slug'];
            }

            // Only update slug if it's unique (excluding this article)
            if ($newSlug && $newSlug !== $article->slug) {
                $slugExists = Article::query()
                    ->where('slug', $newSlug)
                    ->where('id', '!=', $article->id)
                    ->exists();

                if (! $slugExists) {
                    $updateData['slug'] = $newSlug;
                }
            }
        }

        if (array_key_exists('content', $data)) {
            $updateData['content'] = $data['content'] ?? $article->content;
            $updateData['summary'] = $this->generateSummary->handle(
                $data['summary'] ?? null,
                $updateData['content'],
            );
        } elseif (array_key_exists('summary', $data)) {
            $updateData['summary'] = $this->generateSummary->handle(
                $data['summary'] ?? null,
                $article->content,
            );
        }

        if (isset($data['meta'])) {
            $updateData['meta'] = $data['meta'];
        }

        $article->update($updateData);

        $article->categories()->sync($data['categories'] ?? []);

        $article->refresh()->load('categories');

        return view('admin.articles.preview', ['article' => $article]);
    }
}

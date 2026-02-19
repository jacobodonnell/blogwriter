<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateArticlePreviewRequest;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticlePreviewController extends Controller
{
    /**
     * Update article for live preview (AJAX auto-save).
     */
    public function __invoke(UpdateArticlePreviewRequest $request, Article $article): View
    {
        $data = $request->validated();

        $updateData = [];

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        if (isset($data['slug']) && $data['slug'] !== '') {
            // Auto-generate slug from title if slug is a placeholder
            $newSlug = preg_match('/^untitled-[a-z0-9]{8}$/', (string) $data['slug']) && isset($data['title']) && $data['title'] !== ''
                ? Str::slug($data['title'])
                : $data['slug'];

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
        }

        if (array_key_exists('summary', $data)) {
            $updateData['summary'] = $data['summary'] ?? null;
        }

        if (isset($data['meta'])) {
            $updateData['meta'] = $data['meta'];
        }

        // Handle featured image: photo_id takes precedence over URL
        if (array_key_exists('photo_id', $data)) {
            if (! empty($data['photo_id'])) {
                $updateData['photo_id'] = $data['photo_id'];
                // Clear URL-based featured image when selecting a photo
                $meta = $updateData['meta'] ?? $article->meta ?? [];
                unset($meta['featured_image_url']);
                $updateData['meta'] = $meta;
            } else {
                $updateData['photo_id'] = null;
            }
        }

        if (! empty($data['featured_image'])) {
            $meta = $updateData['meta'] ?? $article->meta ?? [];
            $meta['featured_image_url'] = $data['featured_image'];
            $updateData['meta'] = $meta;
            $updateData['photo_id'] = null;
        }

        if (array_key_exists('category_id', $data)) {
            $updateData['category_id'] = $data['category_id'] ?: null;
        }

        $article->update($updateData);

        $article->refresh()->load('category');

        return view('admin.articles.preview', ['article' => $article]);
    }
}

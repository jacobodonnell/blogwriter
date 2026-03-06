<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleRevision;
use App\Services\RevisionService;
use Illuminate\Http\JsonResponse;

final class RevisionController extends Controller
{
    public function __construct(
        private readonly RevisionService $revisionService,
    ) {}

    public function show(Article $article, ArticleRevision $revision): JsonResponse
    {
        abort_unless($revision->article_id === $article->id, 404);

        $content = $this->revisionService->reconstructContent($article, $revision);

        return response()->json([
            'title' => $revision->title,
            'content' => $content,
            'created_at' => $revision->created_at->diffForHumans(),
        ]);
    }

    public function destroy(Article $article, ArticleRevision $revision): JsonResponse
    {
        abort_unless($revision->article_id === $article->id, 404);

        $this->revisionService->deleteRevision($article, $revision);

        return response()->json(['success' => true]);
    }
}

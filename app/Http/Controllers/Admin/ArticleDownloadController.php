<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ArticleDownloadController extends Controller
{
    public function show(Article $article, ArticleExportService $service): StreamedResponse
    {
        $article->loadMissing('user', 'category', 'featuredPhoto.media');

        $filename = $article->slug.'.md';
        $content = $service->buildArticleMarkdown($article);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/markdown',
        ]);
    }
}

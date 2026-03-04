<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleExportService;
use App\Services\PhotoExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

final class ArticleExportSingleController extends Controller
{
    public function __invoke(
        Article $article,
        ArticleExportService $exportService,
        PhotoExportService $photoService,
    ): StreamedResponse {
        $article->loadMissing('category', 'featuredPhoto.media');

        $filename = $article->slug.'-export-'.now()->format('Y-m-d').'.zip';

        return response()->streamDownload(function () use ($article, $exportService, $photoService): void {
            $zip = new ZipStream(
                defaultEnableZeroHeader: false,
                sendHttpHeaders: false,
                outputName: null,
            );

            $zip->addFile(
                sprintf('articles/%s.md', $article->slug),
                $exportService->buildArticleMarkdown($article),
            );

            $exportService->streamArticleCategoryToZip($zip, $article);

            if ($article->featuredPhoto) {
                $photoService->buildPhotoZip($article->featuredPhoto, $zip);
            }

            $zip->finish();
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleExportService;
use App\Services\PhotoExportService;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

final class ArticleDownloadController extends Controller
{
    public function __invoke(
        Article $article,
        ArticleExportService $exportService,
        PhotoExportService $photoService,
    ): StreamedResponse {
        $article->loadMissing('category', 'featuredPhoto.media', 'revisions');

        $filename = $article->slug.'-export-'.now()->format('Y-m-d').'.zip';

        return response()->streamDownload(function () use ($article, $exportService, $photoService): void {
            $zip = new ZipStream(
                defaultEnableZeroHeader: false,
                sendHttpHeaders: false,
                outputName: null,
            );

            $exportService->streamToZip($zip, new Collection([$article]));

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

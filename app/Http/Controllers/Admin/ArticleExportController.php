<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleExportService;
use App\Services\PhotoExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

final class ArticleExportController extends Controller
{
    public function store(ArticleExportService $exportService, PhotoExportService $photoService): StreamedResponse
    {
        $articles = Article::query()
            ->with('category', 'featuredPhoto.media', 'revisions')
            ->orderBy('created_at')
            ->get();

        $filename = 'blogwriter-export-'.now()->format('Y-m-d').'.zip';

        return response()->streamDownload(function () use ($articles, $exportService, $photoService): void {
            $zip = new ZipStream(
                defaultEnableZeroHeader: false,
                sendHttpHeaders: false,
                outputName: null,
            );

            $exportService->streamToZip($zip, $articles);
            $exportService->streamCategoriesToZip($zip);
            $exportService->streamSettingsToZip($zip);

            $photoService->streamPhotosToZip($zip);
            $photoService->streamPhotoImagesToZip($zip);

            $zip->finish();
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}

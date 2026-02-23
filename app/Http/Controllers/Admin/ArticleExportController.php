<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

final class ArticleExportController extends Controller
{
    public function store(ArticleExportService $exportService): StreamedResponse
    {
        $articles = Article::query()
            ->with('user', 'category', 'featuredPhoto.media')
            ->orderBy('created_at')
            ->get();

        $filename = 'blogwriter-export-'.now()->format('Y-m-d').'.zip';

        return response()->streamDownload(function () use ($articles, $exportService): void {
            $zip = new ZipStream(
                defaultEnableZeroHeader: false,
                sendHttpHeaders: false,
                outputName: null,
            );

            $exportService->streamToZip($zip, $articles);
            $exportService->streamCategoriesToZip($zip);
            $exportService->streamSettingsToZip($zip);

            $zip->finish();
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}

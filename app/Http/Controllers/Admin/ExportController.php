<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

final class ExportController extends Controller
{
    public function index(): View
    {
        return view('admin.export.index', [
            'articleCount' => Article::query()->count(),
        ]);
    }

    public function articles(Request $request, ArticleExportService $exportService): StreamedResponse
    {
        $articles = Article::query()
            ->with('user', 'category')
            ->orderBy('created_at')
            ->get();

        $filename = 'blogwriter-export-'.now()->format('Y-m-d').'.zip';

        return response()->streamDownload(function () use ($articles, $exportService): void {
            $zip = new ZipStream(
                outputName: null,
                sendHttpHeaders: false,
            );

            $exportService->streamToZip($zip, $articles);

            $zip->finish();
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}

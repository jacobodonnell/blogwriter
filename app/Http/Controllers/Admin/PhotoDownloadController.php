<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Services\PhotoExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

final class PhotoDownloadController extends Controller
{
    public function show(Photo $photo, PhotoExportService $service): StreamedResponse
    {
        $filename = $photo->slug.'.zip';

        return response()->streamDownload(function () use ($photo, $service): void {
            $zip = new ZipStream(
                defaultEnableZeroHeader: false,
                sendHttpHeaders: false,
                outputName: null,
            );

            $service->buildPhotoZip($photo, $zip);

            $zip->finish();
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}

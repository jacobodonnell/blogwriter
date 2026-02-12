<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;

final readonly class ExtractExifDataAction
{
    public function handle(UploadedFile $file): array
    {
        if (! function_exists('exif_read_data')) {
            return [];
        }

        $exif = @exif_read_data($file->getPathname());

        if (! $exif) {
            return [];
        }

        return array_filter([
            'camera_model' => $exif['Model'] ?? null,
            'lens' => $exif['LensModel'] ?? null,
            'iso' => $exif['ISOSpeedRatings'] ?? null,
            'aperture' => $exif['FNumber'] ?? null,
            'shutter_speed' => $exif['ExposureTime'] ?? null,
            'width' => $exif['COMPUTED']['Width'] ?? null,
            'height' => $exif['COMPUTED']['Height'] ?? null,
        ]);
    }
}

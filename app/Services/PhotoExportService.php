<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Photo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Yaml\Yaml;
use ZipStream\ZipStream;

final class PhotoExportService
{
    /**
     * Stream all photos as a photos.yaml file into the given ZipStream.
     */
    public function streamPhotosToZip(ZipStream $zip): void
    {
        $photos = Photo::query()->with('category', 'media')->orderBy('id')->get();

        $data = $photos->map(fn (Photo $photo): array => array_filter([
            'slug' => $photo->slug,
            'filename' => $photo->filename,
            'caption' => $photo->caption,
            'alt_text' => $photo->alt_text,
            'status' => $photo->status->value,
            'published_at' => $photo->published_at?->utc()->toIso8601String(),
            'taken_at' => $photo->taken_at?->utc()->toIso8601String(),
            'category' => $photo->category?->slug,
            'meta' => $photo->meta ?: null,
            'image_file' => $this->photoImageFilename($photo),
        ], fn ($v): bool => $v !== null))->values()->all();

        $zip->addFile('photos.yaml', Yaml::dump($data, 3, 2));
    }

    /**
     * Stream original photo image files into the images/ directory in the ZipStream.
     */
    public function streamPhotoImagesToZip(ZipStream $zip): void
    {
        $photos = Photo::query()->with('media')->get();

        foreach ($photos as $photo) {
            $this->streamPhotoImageToZip($zip, $photo);
        }
    }

    /**
     * Stream a single photo's YAML metadata and image file into the ZipStream.
     */
    public function buildPhotoZip(Photo $photo, ZipStream $zip): void
    {
        $photo->loadMissing('category', 'media');

        $data = array_filter([
            'slug' => $photo->slug,
            'filename' => $photo->filename,
            'caption' => $photo->caption,
            'alt_text' => $photo->alt_text,
            'status' => $photo->status->value,
            'published_at' => $photo->published_at?->utc()->toIso8601String(),
            'taken_at' => $photo->taken_at?->utc()->toIso8601String(),
            'category' => $photo->category?->slug,
            'meta' => $photo->meta ?: null,
            'image_file' => $this->photoImageFilename($photo),
        ], fn ($v): bool => $v !== null);

        $zip->addFile('photos.yaml', Yaml::dump([$data], 3, 2));

        $this->streamPhotoImageToZip($zip, $photo);
    }

    /**
     * Build the filename used for a photo's image file in the export.
     */
    public function photoImageFilename(Photo $photo): ?string
    {
        $media = $photo->getFirstMedia('image');
        if (! $media instanceof Media) {
            return null;
        }

        return $media->uuid.'-'.$media->file_name;
    }

    /**
     * Stream a single photo's image file into the images/ directory in the ZipStream.
     */
    private function streamPhotoImageToZip(ZipStream $zip, Photo $photo): void
    {
        $media = $photo->getFirstMedia('image');
        if (! $media instanceof Media) {
            return;
        }

        $path = $media->getPath();
        if (! file_exists($path)) {
            return;
        }

        $zip->addFileFromPath(
            'images/'.$this->photoImageFilename($photo),
            $path,
        );
    }
}

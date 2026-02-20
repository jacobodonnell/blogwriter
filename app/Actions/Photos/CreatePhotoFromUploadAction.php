<?php

namespace App\Actions\Photos;

use App\Actions\GenerateUniqueSlugAction;
use App\Enums\Status;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;

final readonly class CreatePhotoFromUploadAction
{
    public function __construct(
        private ExtractExifDataAction $extractExif,
        private GenerateUniqueSlugAction $generateSlug
    ) {}

    public function handle(UploadedFile $file, array $attributes): Photo
    {
        $filename = $file->getClientOriginalName();
        $baseSlug = $attributes['slug'] ?? pathinfo($filename, PATHINFO_FILENAME);
        $exifData = $this->extractExif->handle($file);

        $photo = Photo::create([
            'user_id' => $attributes['user_id'] ?? auth()->id(),
            'filename' => $filename,
            'slug' => $this->generateSlug->handle($baseSlug, Photo::class),
            'alt_text' => $attributes['alt_text'] ?? null,
            'caption' => $attributes['caption'] ?? null,
            'status' => $attributes['status'] ?? Status::Draft,
            'published_at' => ($attributes['status'] ?? Status::Draft) === Status::Published ? now() : null,
            'taken_at' => $attributes['taken_at'] ?? null,
            'category_id' => $attributes['category_id'] ?? null,
            'meta' => $exifData,
        ]);

        // Add file to MediaLibrary
        $disk = $photo->status->isPublic() ? 'public' : 'private';
        $photo->addMedia($file)->toMediaCollection('image', $disk);

        return $photo;
    }
}

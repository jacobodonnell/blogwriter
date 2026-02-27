<?php

declare(strict_types=1);

namespace App\Actions\Photos;

use App\Enums\Status;
use App\Exceptions\PhotoUploadFailedException;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final readonly class HandleArticlePhotoUploadAction
{
    public function __construct(
        private CreatePhotoFromUploadAction $createPhotoFromUpload,
    ) {}

    /**
     * Handle photo upload and return photo ID.
     *
     * @throws PhotoUploadFailedException
     */
    public function handle(UploadedFile $file, array $data, ?int $articleId = null): int
    {
        try {
            $photo = $this->createPhotoFromUpload->handle($file, [
                'slug' => $data['slug'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'alt_text' => $data['featured_image_alt'] ?? $data['title'] ?? 'Featured image',
                'caption' => $data['featured_image_caption'] ?? null,
                'status' => Status::Public,
            ]);

            return $photo->id;
        } catch (Exception $exception) {
            Log::error('Failed to create photo from upload', [
                'article_id' => $articleId,
                'error' => $exception->getMessage(),
            ]);

            throw new PhotoUploadFailedException($exception->getMessage(), $exception->getCode(), previous: $exception);
        }
    }
}

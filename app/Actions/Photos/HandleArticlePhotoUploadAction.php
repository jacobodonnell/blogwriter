<?php

namespace App\Actions\Photos;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

final readonly class HandleArticlePhotoUploadAction
{
    public function __construct(
        private CreatePhotoFromUploadAction $createPhotoFromUpload,
    ) {}

    /**
     * Handle photo upload and return photo ID or error redirect.
     */
    public function handle(UploadedFile $file, array $data, ?int $articleId = null): int|RedirectResponse
    {
        try {
            $photo = $this->createPhotoFromUpload->handle($file, [
                'slug' => $data['slug'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'alt_text' => $data['featured_image_alt'] ?? $data['title'] ?? 'Featured image',
                'caption' => $data['featured_image_caption'] ?? null,
                'status' => 'published',
            ]);

            return $photo->id;
        } catch (\Exception $exception) {
            Log::error('Failed to create photo from upload', [
                'article_id' => $articleId,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['featured_image_file' => 'Failed to upload image. Please try again.']);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\Photos\HandleArticlePhotoUploadAction;
use App\Exceptions\PhotoUploadFailedException;
use App\Models\Article;
use Illuminate\Http\Request;

final readonly class ApplyArticleFeaturedImageAction
{
    public function __construct(
        private HandleArticlePhotoUploadAction $handlePhotoUpload,
        private NormalizeCaptionMetaAction $normalizeCaptionMeta,
    ) {}

    /**
     * Resolve featured image fields from the request.
     *
     * @param  array<string, mixed>  $data  Validated request data
     * @return array{photo_id: int|null, meta: array<string, mixed>}
     *
     * @throws PhotoUploadFailedException
     */
    public function handle(Request $request, array $data, ?Article $article = null): array
    {
        $photoId = $article?->photo_id;
        $meta = $data['meta'] ?? [];

        if ($request->boolean('remove_featured_image')) {
            $photoId = null;
            unset($meta['featured_image_url'], $meta['featured_image_caption'], $meta['use_photo_caption']);
        } elseif ($request->hasFile('featured_image_file')) {
            $photoId = $this->handlePhotoUpload->handle(
                $request->file('featured_image_file'),
                array_merge($data, $request->only('featured_image_alt', 'featured_image_caption')),
                $article?->id,
            );

            unset($meta['featured_image_url']);
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            $meta['featured_image_url'] = $request->featured_image;
            $photoId = null;
        } elseif ($request->filled('photo_id')) {
            $photoId = $data['photo_id'];
            unset($meta['featured_image_url']);
        } elseif ($article instanceof Article) {
            // Preserve existing meta featured_image_url if present
            $existingMeta = $article->meta ?? [];
            if (isset($existingMeta['featured_image_url']) && ! isset($meta['featured_image_url'])) {
                $meta['featured_image_url'] = $existingMeta['featured_image_url'];
            }
        }

        $meta = $this->normalizeCaptionMeta->handle($meta, $photoId, $meta['featured_image_url'] ?? null);

        return [
            'photo_id' => $photoId,
            'meta' => $meta,
        ];
    }
}

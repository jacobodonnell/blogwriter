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
     * @return array{photo_id: int|null, external_featured_img_url: string|null, meta: array<string, mixed>}
     *
     * @throws PhotoUploadFailedException
     */
    public function handle(Request $request, array $data, ?Article $article = null): array
    {
        $photoId = $article?->photo_id;
        $externalUrl = $article?->external_featured_img_url;
        $meta = $data['meta'] ?? [];

        if ($request->boolean('remove_featured_image')) {
            $photoId = null;
            $externalUrl = null;
            unset($meta['featured_image_caption'], $meta['use_photo_caption'], $meta['featured_image_alt']);
        } elseif ($request->hasFile('featured_image_file')) {
            $photoId = $this->handlePhotoUpload->handle(
                $request->file('featured_image_file'),
                array_merge($data, $request->only('featured_image_alt', 'featured_image_caption')),
                $article?->id,
            );

            $externalUrl = null;
            $meta = $this->syncAltTextMeta($meta, $data);
        } elseif ($request->filled('featured_image') && filter_var($request->featured_image, FILTER_VALIDATE_URL)) {
            $externalUrl = $request->featured_image;
            $photoId = null;
            $meta['featured_image_alt'] = ($data['featured_image_alt'] ?? '') ?: ($data['title'] ?? '');
        } elseif ($request->filled('photo_id')) {
            $photoId = (int) $data['photo_id'];
            $externalUrl = null;
            $meta = $this->syncAltTextMeta($meta, $data);
        }

        $meta = $this->normalizeCaptionMeta->handle($meta, $photoId, $externalUrl);

        return [
            'photo_id' => $photoId,
            'external_featured_img_url' => $externalUrl,
            'meta' => $meta,
        ];
    }

    /**
     * Set or clear the featured_image_alt meta key from request data.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function syncAltTextMeta(array $meta, array $data): array
    {
        if (! empty($data['featured_image_alt'])) {
            $meta['featured_image_alt'] = $data['featured_image_alt'];
        } else {
            unset($meta['featured_image_alt']);
        }

        return $meta;
    }
}

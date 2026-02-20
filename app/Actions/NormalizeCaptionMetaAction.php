<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class NormalizeCaptionMetaAction
{
    /**
     * Normalize caption meta keys for mutual exclusion.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function handle(array $meta, ?int $photoId, ?string $featuredImageUrl = null): array
    {
        // Caption meta mutual exclusion
        if (! empty($meta['use_photo_caption'])) {
            unset($meta['featured_image_caption']);
        } else {
            unset($meta['use_photo_caption']);
        }

        // Clear caption keys if no featured image at all
        if (($photoId === null || $photoId === 0) && in_array($featuredImageUrl, [null, '', '0'], true)) {
            unset($meta['featured_image_caption'], $meta['use_photo_caption']);
        }

        return $meta;
    }
}

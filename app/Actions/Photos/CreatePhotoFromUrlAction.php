<?php

namespace App\Actions\Photos;

use App\Actions\GenerateUniqueSlugAction;
use App\Models\Photo;

final readonly class CreatePhotoFromUrlAction
{
    public function __construct(
        private GenerateUniqueSlugAction $generateSlug
    ) {}

    public function handle(string $url, array $attributes): Photo
    {
        $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'external-image.jpg';
        $baseSlug = $attributes['slug'] ?? pathinfo($filename, PATHINFO_FILENAME);

        return Photo::create([
            'filename' => $filename,
            'slug' => $this->generateSlug->handle($baseSlug, Photo::class),
            'alt_text' => $attributes['alt_text'] ?? null,
            'caption' => $attributes['caption'] ?? null,
            'status' => $attributes['status'] ?? 'draft',
            'published_at' => ($attributes['status'] ?? 'draft') === 'published' ? now() : null,
            'meta' => ['external_url' => $url],
        ]);
    }
}

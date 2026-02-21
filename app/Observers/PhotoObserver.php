<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Photo;

final class PhotoObserver
{
    /**
     * Handle the Photo "updated" event.
     */
    public function updated(Photo $photo): void
    {
        if (! $photo->wasChanged('status')) {
            return;
        }

        // Auto-set published_at when publishing for the first time
        if ($photo->status->isPublic() && ! $photo->published_at) {
            $photo->published_at = now();
            $photo->saveQuietly();
        }

        // Clear published_at when unpublishing
        if ($photo->status->isPrivate() && $photo->published_at) {
            $photo->published_at = null;
            $photo->saveQuietly();
        }

        // Detach photo from articles when switching to draft
        if ($photo->status->isPrivate()) {
            $photo->articles()->update(['photo_id' => null]);
        }

        // Move media between disks based on status
        if ($photo->hasMedia('image')) {
            $media = $photo->getFirstMedia('image');
            $expectedDisk = $photo->status->isPublic() ? 'public' : 'private';

            if ($media->disk !== $expectedDisk) {
                $media->move($photo, 'image', $expectedDisk);
            }
        }
    }
}

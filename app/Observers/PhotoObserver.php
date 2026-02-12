<?php

namespace App\Observers;

use App\Models\Photo;

class PhotoObserver
{
    /**
     * Handle the Photo "updated" event.
     */
    public function updated(Photo $photo): void
    {
        // Auto-set published_at when publishing
        if ($photo->wasChanged('status') && $photo->status->isPublic() && ! $photo->published_at) {
            $photo->published_at = now();
            $photo->saveQuietly();
        }

        // Move media between disks based on status
        if ($photo->wasChanged('status') && $photo->hasMedia('image')) {
            $media = $photo->getFirstMedia('image');
            $expectedDisk = $photo->status->isPublic() ? 'public' : 'private';

            if ($media->disk !== $expectedDisk) {
                $media->move($photo, 'image', $expectedDisk);
            }
        }
    }
}

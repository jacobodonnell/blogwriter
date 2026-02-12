<?php

namespace App\Observers;

use App\Enums\Status;
use App\Models\Article;

class ArticleObserver
{
    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        if ($article->wasChanged('status')) {
            $media = $article->getFirstMedia('featured_image');

            if ($media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
                $expectedDisk = $article->status === Status::Published ? 'public' : 'private';

                if ($media->disk !== $expectedDisk) {
                    $media->move($article, 'featured_image', $expectedDisk);
                }
            }
        }
    }
}

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
        // Auto-set published_at when publishing
        if ($article->wasChanged('status') && $article->status === Status::Published && $article->published_at === null) {
            $article->published_at = now();
            $article->saveQuietly();
        }

        // Media migration now handled by PhotoObserver
    }
}

<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

final readonly class UpdatePublishedStatusAction
{
    public function handle(Model $model, string $status): void
    {
        if ($status === 'published' && $model->published_at === null) {
            $model->published_at = now();
        } elseif ($status !== 'published') {
            $model->published_at = null;
        }
    }
}

<?php

namespace App\Actions;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;

final readonly class UpdatePublishedStatusAction
{
    public function handle(Model $model, Status $status): void
    {
        if ($status === Status::Published && $model->published_at === null) {
            $model->published_at = now();
        } elseif ($status !== Status::Published) {
            $model->published_at = null;
        }
    }
}

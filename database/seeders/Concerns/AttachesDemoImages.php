<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Enums\Status;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait AttachesDemoImages
{
    /**
     * Attach a demo image to a model (Article or Photo).
     */
    protected function attachDemoImage(Model $model, int $imageNumber, string $collection = 'image'): void
    {
        $demoImagesPath = database_path('seeders/demo-images');
        $imagePath = $demoImagesPath.'/demo-image-'.$imageNumber.'.jpg';

        if (! file_exists($imagePath) || filesize($imagePath) === 0) {
            Log::warning('Demo image file not found or empty', [
                'model_class' => $model::class,
                'model_id' => $model->id,
                'image_path' => $imagePath,
            ]);

            return;
        }

        $disk = $model->status === Status::Public ? 'public' : 'private';

        try {
            $model->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection($collection, $disk);
        } catch (Exception $exception) {
            Log::warning('Failed to attach demo image to model', [
                'model_class' => $model::class,
                'model_id' => $model->id,
                'image_path' => $imagePath,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

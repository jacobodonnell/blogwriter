<?php

namespace Database\Factories\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait AttachesFeaturedImages
{
    /**
     * Attach a demo image to a model (Article or Photo).
     */
    protected function attachDemoImage(Model $model, int $imageNumber, string $collection = 'image'): void
    {
        $demoImagesPath = database_path('seeders/demo-images');
        $imagePath = $demoImagesPath.'/demo-image-'.$imageNumber.'.png';

        // Validate file exists and is not empty
        if (! file_exists($imagePath) || filesize($imagePath) === 0) {
            Log::warning('Demo image file not found or empty', [
                'model_class' => $model::class,
                'model_id' => $model->id,
                'image_path' => $imagePath,
            ]);

            return;
        }

        // Determine disk based on model status
        $disk = $model->status === \App\Enums\Status::Published ? 'public' : 'private';

        try {
            $model->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection($collection, $disk);
        } catch (\Exception $exception) {
            Log::warning('Failed to attach demo image to model', [
                'model_class' => $model::class,
                'model_id' => $model->id,
                'image_path' => $imagePath,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Attach a picsum image to a model using a seed.
     */
    protected function attachPicsumImage(Model $model, string $seed): void
    {
        // Note: This method is a placeholder for future implementation
        // Picsum.photos URLs are not suitable for seeding since they require external network access
        // For now, we'll just log a warning
        Log::info('Picsum image attachment requested but not implemented', [
            'model_class' => $model::class,
            'model_id' => $model->id,
            'seed' => $seed,
        ]);
    }
}

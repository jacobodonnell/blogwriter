<?php

namespace Database\Factories\Concerns;

use App\Models\Article;
use Illuminate\Support\Facades\Log;

trait AttachesFeaturedImages
{
    /**
     * Attach a demo image to an article.
     */
    protected function attachDemoImage(Article $article, int $imageNumber): void
    {
        $demoImagesPath = database_path('seeders/demo-images');
        $imagePath = $demoImagesPath.'/demo-image-'.$imageNumber.'.png';

        // Validate file exists and is not empty
        if (! file_exists($imagePath) || filesize($imagePath) === 0) {
            Log::warning('Demo image file not found or empty', [
                'article_id' => $article->id,
                'image_path' => $imagePath,
            ]);

            return;
        }

        // Determine disk based on article status
        $disk = $article->status === \App\Enums\Status::Published ? 'public' : 'private';

        try {
            $article->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection('featured_image', $disk);
        } catch (\Exception $e) {
            Log::warning('Failed to attach demo image to article', [
                'article_id' => $article->id,
                'image_path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Attach a picsum image to an article using a seed.
     */
    protected function attachPicsumImage(Article $article, string $seed): void
    {
        // Note: This method is a placeholder for future implementation
        // Picsum.photos URLs are not suitable for seeding since they require external network access
        // For now, we'll just log a warning
        Log::info('Picsum image attachment requested but not implemented', [
            'article_id' => $article->id,
            'seed' => $seed,
        ]);
    }
}

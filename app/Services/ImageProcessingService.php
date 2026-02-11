<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageProcessingService
{
    private readonly ImageManager $manager;

    /**
     * Maximum dimension for the longest side (WordPress-style).
     */
    private const MAX_DIMENSION = 2560;

    /**
     * Image size definitions.
     *
     * @var array<string, array{width: int, height: int, crop: bool}>
     */
    private const SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium' => ['width' => 300, 'height' => 300, 'crop' => false],
        'large' => ['width' => 1024, 'height' => 1024, 'crop' => false],
    ];

    /**
     * WebP quality (0-100).
     */
    private const WEBP_QUALITY = 85;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Get the image manager instance.
     */
    public function getManager(): ImageManager
    {
        return $this->manager;
    }

    /**
     * Process and convert uploaded image to WebP with multiple sizes.
     *
     * @return array<string, mixed>
     */
    public function processUpload(Article $article, string $sourcePath, string $disk): array
    {
        try {
            if (! $article->id) {
                throw new \RuntimeException('Article must be saved before processing images');
            }

            // Validate source file exists
            if (! Storage::disk($disk)->exists($sourcePath)) {
                \Log::warning('Source image not found', ['path' => $sourcePath, 'disk' => $disk]);

                return null;
            }

            $baseDir = "articles/featured/{$article->id}";

            // Read source image
            $sourceImage = $this->manager->read(Storage::disk($disk)->path($sourcePath));

            // Get original dimensions
            $originalWidth = $sourceImage->width();
            $originalHeight = $sourceImage->height();

            // Pre-flight dimension check
            if ($originalWidth > 10000 || $originalHeight > 10000) {
                \Log::error('Image dimensions too large', [
                    'width' => $originalWidth,
                    'height' => $originalHeight,
                    'path' => $sourcePath,
                    'article_id' => $article->id,
                ]);

                return null;
            }

            // Pre-flight memory check (rough estimate: width × height × 4 bytes × 5 copies)
            $estimatedMemory = ($originalWidth * $originalHeight * 4 * 5) / 1024 / 1024; // MB
            $memoryLimit = ini_get('memory_limit');
            $availableMemory = $memoryLimit === '-1' ? PHP_INT_MAX : (int) $memoryLimit;

            if ($estimatedMemory > ($availableMemory * 0.7)) {
                \Log::error('Insufficient memory for image processing', [
                    'estimated_mb' => $estimatedMemory,
                    'available_mb' => $availableMemory,
                    'path' => $sourcePath,
                    'article_id' => $article->id,
                ]);

                return null;
            }

            // Scale down if needed (WordPress-style 2560px limit)
            $scaledDimensions = $this->scaleDownIfNeeded($originalWidth, $originalHeight);
            $scaledWidth = $scaledDimensions['width'];
            $scaledHeight = $scaledDimensions['height'];

            // Create the full-size image (scaled if needed)
            $fullImage = clone $sourceImage;
            if ($scaledWidth !== $originalWidth || $scaledHeight !== $originalHeight) {
                $fullImage->scale($scaledWidth, $scaledHeight);
            }

            // Store the main image (full size)
            $mainPath = "{$baseDir}/full.webp";
            $this->storeWebp($fullImage, $mainPath, $disk);

            // Generate and store size variants using suffix naming (full-thumbnail.webp, etc.)
            $generatedSizes = [];
            foreach (self::SIZES as $sizeName => $dimensions) {
                // Use suffix naming: full-{size}.webp
                $sizePath = "{$baseDir}/full-{$sizeName}.webp";
                $this->generateSize($sourceImage, $sizePath, $dimensions, $disk);
                $generatedSizes[$sizeName] = $sizePath;
            }

            // Verify all WebP files exist before deleting original
            $expectedFiles = [$mainPath];
            foreach ($generatedSizes as $file) {
                $expectedFiles[] = $file;
            }

            foreach ($expectedFiles as $file) {
                if (! Storage::disk($disk)->exists($file)) {
                    \Log::error('WebP file missing after processing', [
                        'file' => $file,
                        'article_id' => $article->id,
                    ]);
                    throw new \RuntimeException("Failed to create {$file}");
                }
            }

            // NOW delete original (only if all WebP files confirmed)
            if (Storage::disk($disk)->exists($sourcePath)) {
                Storage::disk($disk)->delete($sourcePath);
            }

            // Get file size of the main image
            $fileSize = Storage::disk($disk)->size($mainPath);

            return [
                'path' => $mainPath,
                'width' => $scaledWidth,
                'height' => $scaledHeight,
                'file_size' => $fileSize,
                'mime_type' => 'image/webp',
                'sizes' => $generatedSizes,
            ];
        } catch (\Exception $e) {
            \Log::error('Image processing exception', [
                'source' => $sourcePath,
                'disk' => $disk,
                'article_id' => $article->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete all processed images for an article.
     */
    public function deleteImages(Article $article, string $disk): void
    {
        if (! $article->featured_image) {
            return;
        }

        // Delete all size variants
        $baseDir = "articles/featured/{$article->id}";
        Storage::disk($disk)->deleteDirectory($baseDir);

        // Clear article metadata
        $article->featured_image = null;
        $article->featured_image_width = null;
        $article->featured_image_height = null;
        $article->featured_image_file_size = null;
        $article->featured_image_mime_type = null;
    }

    /**
     * Move images between disks (public <-> private).
     */
    public function moveImages(Article $article, string $fromDisk, string $toDisk): void
    {
        if (! $article->featured_image || Str::isUrl($article->featured_image)) {
            return;
        }

        // Skip if source and destination are the same disk
        if ($fromDisk === $toDisk) {
            return;
        }

        $baseDir = "articles/featured/{$article->id}";
        $files = [
            'full.webp',
            'full-thumbnail.webp',
            'full-medium.webp',
            'full-large.webp',
        ];

        // Copy all size variants
        foreach ($files as $file) {
            $path = "{$baseDir}/{$file}";
            if (Storage::disk($fromDisk)->exists($path)) {
                $content = Storage::disk($fromDisk)->get($path);
                Storage::disk($toDisk)->put($path, $content);
            }
        }

        // Delete from source disk
        Storage::disk($fromDisk)->deleteDirectory($baseDir);
    }

    /**
     * Delete old images when replacing.
     */
    public function deleteOldImages(?string $oldPath, string $disk): void
    {
        if (! $oldPath) {
            return;
        }

        $baseDir = dirname($oldPath);
        if ($baseDir && $baseDir !== '.' && $baseDir !== '/') {
            Storage::disk($disk)->deleteDirectory($baseDir);
        }
    }

    /**
     * Check if an image file is valid/readable.
     */
    public function isValidImage(string $path, string $disk): bool
    {
        try {
            $fullPath = Storage::disk($disk)->path($path);
            if (! file_exists($fullPath) || filesize($fullPath) === 0) {
                return false;
            }
            $this->manager->read($fullPath);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Scale down dimensions if they exceed max limit.
     *
     * @return array{width: int, height: int}
     */
    private function scaleDownIfNeeded(int $width, int $height): array
    {
        $maxSide = max($width, $height);

        if ($maxSide <= self::MAX_DIMENSION) {
            return ['width' => $width, 'height' => $height];
        }

        // Calculate scale factor
        $scale = self::MAX_DIMENSION / $maxSide;

        return [
            'width' => (int) round($width * $scale),
            'height' => (int) round($height * $scale),
        ];
    }

    /**
     * Generate a sized variant of the image.
     *
     * @param  \Intervention\Image\Image  $image
     * @param  array{width: int, height: int, crop: bool}  $dimensions
     */
    private function generateSize(\Intervention\Image\Interfaces\ImageInterface $image, string $path, array $dimensions, string $disk): void
    {
        $clone = clone $image;
        $originalWidth = $clone->width();
        $originalHeight = $clone->height();

        // Check if image is smaller than target dimensions
        if ($originalWidth <= $dimensions['width'] && $originalHeight <= $dimensions['height']) {
            // Don't enlarge - keep original size
            $this->storeWebp($clone, $path, $disk);

            return;
        }

        if ($dimensions['crop']) {
            // Hard crop to exact dimensions (center crop)
            $clone->cover($dimensions['width'], $dimensions['height']);
        } else {
            // Soft resize - maintain aspect ratio within max dimensions
            $clone->scaleDown($dimensions['width'], $dimensions['height']);
        }

        $this->storeWebp($clone, $path, $disk);
    }

    /**
     * Store image as WebP format.
     *
     * @param  \Intervention\Image\Image  $image
     */
    private function storeWebp(\Intervention\Image\Interfaces\ImageInterface $image, string $path, string $disk): void
    {
        try {
            $encoded = $image->toWebp(self::WEBP_QUALITY);
            $encodedString = $encoded->toString();

            // Verify we got valid data
            if (empty($encodedString)) {
                throw new \RuntimeException('WebP encoding produced empty output');
            }

            Storage::disk($disk)->put($path, $encodedString);

            // Verify file was written successfully
            if (! Storage::disk($disk)->exists($path)) {
                throw new \RuntimeException('File not found after write operation');
            }
        } catch (\Exception $e) {
            \Log::error('WebP storage failed', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw to let caller handle
        }
    }
}

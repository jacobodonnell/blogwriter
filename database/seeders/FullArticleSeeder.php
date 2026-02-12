<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Database\Factories\Concerns\AttachesFeaturedImages;
use Illuminate\Database\Seeder;

class FullArticleSeeder extends Seeder
{
    use AttachesFeaturedImages;

    /**
     * Seed full articles (15 articles from JSON: 8 published, 7 draft after hidden→draft conversion).
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/blogwriter/test-data/full/articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        // Demo image counter for cycling through 5 demo images
        $demoImages = [1, 2, 3, 4, 5];
        $imageCounter = 0;

        // Process articles: assign local images to first 7, Picsum URLs to last 7, skip the null ones
        $articlesWithImages = array_filter($articles, fn ($a) => $a['featured_image'] !== null);
        $halfCount = (int) ceil(count($articlesWithImages) / 2);

        foreach ($articles as $index => $data) {
            // Convert hidden status to draft (hidden status removed in refactoring)
            $status = match ($data['status']) {
                'published' => 'published',
                'draft' => 'draft',
                'hidden' => 'draft', // Convert hidden → draft
                default => 'draft',
            };

            $article = Article::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'summary' => $data['summary'] ?? null,
                    'status' => $status,
                    'published_at' => $status === 'published' ? now()->subDays(rand(1, 30)) : null,
                    'meta' => $data['meta'] ?? null,
                ]
            );

            // Attach categories if not already attached
            if ($article->categories()->count() === 0 && isset($data['categories'])) {
                $categoryIds = Category::whereIn('name', $data['categories'])->pluck('id');
                $article->categories()->attach($categoryIds);
            }

            // Handle featured images based on strategy
            if ($data['featured_image'] === null) {
                // No image - skip
                continue;
            }

            // Determine if this article should get local demo image or Picsum URL
            $articlesWithImagesIndexed = array_values($articlesWithImages);
            $positionInImagedArticles = array_search($data, $articlesWithImagesIndexed, true);

            if ($positionInImagedArticles !== false && $positionInImagedArticles < $halfCount) {
                // First 50%: Use local demo images
                if (! $article->hasMedia('featured_image')) {
                    $demoImageNum = $demoImages[$imageCounter % count($demoImages)];
                    $this->attachDemoImage($article, $demoImageNum);
                    $imageCounter++;
                }
            } else {
                // Second 50%: Store Picsum URL in meta
                if (! isset($article->meta['featured_image'])) {
                    $meta = $article->meta ?? [];
                    $meta['featured_image'] = $data['featured_image'];
                    $article->update(['meta' => $meta]);
                }
            }
        }
    }
}

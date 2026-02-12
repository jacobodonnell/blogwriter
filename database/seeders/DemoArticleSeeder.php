<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Database\Factories\Concerns\AttachesFeaturedImages;
use Illuminate\Database\Seeder;

class DemoArticleSeeder extends Seeder
{
    use AttachesFeaturedImages;

    /**
     * Seed demo articles (5 articles from JSON: 4 published, 1 draft).
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/blogwriter/test-data/demo/articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        // Demo image counter for cycling through 5 demo images
        $demoImages = [1, 2, 3, 4, 5];
        $imageCounter = 0;

        // Process articles: assign local images to first 2, Picsum URLs to last 2, skip the null one
        $articlesWithImages = array_filter($articles, fn ($a) => $a['featured_image'] !== null);
        $halfCount = (int) ceil(count($articlesWithImages) / 2);

        foreach ($articles as $index => $data) {
            // Convert status (no hidden status exists in demo)
            $status = $data['status'] === 'published' ? 'published' : 'draft';

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

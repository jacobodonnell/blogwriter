<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use Database\Factories\Concerns\AttachesFeaturedImages;
use Illuminate\Database\Seeder;

class FullArticleSeeder extends Seeder
{
    use AttachesFeaturedImages;

    /**
     * Seed full articles (15 articles from JSON: 8 published, 7 draft after hidden->draft conversion).
     */
    public function run(): void
    {
        $jsonPath = storage_path('app/blogwriter/test-data/full/articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        $user = User::firstOrFail();

        // Demo image counter for cycling through 5 demo images
        $demoImages = [1, 2, 3, 4, 5];
        $imageCounter = 0;

        foreach ($articles as $data) {
            // Convert hidden status to draft (hidden status removed in refactoring)
            $status = match ($data['status']) {
                'published' => 'published',
                default => 'draft',
            };

            // Handle photo creation — only local demo images, external URLs go to meta
            $photoId = null;
            $meta = $data['meta'] ?? [];

            if ($data['featured_image'] !== null) {
                if ($this->isExternalUrl($data['featured_image'])) {
                    // Store external URL in article meta instead of creating a Photo
                    $meta['featured_image_url'] = $data['featured_image'];
                } else {
                    // Use local demo images
                    $demoImageNum = $demoImages[$imageCounter % count($demoImages)];
                    $photo = Photo::factory()
                        ->state([
                            'user_id' => $user->id,
                            'status' => $status,
                            'published_at' => $status === 'published' ? now()->subDays(random_int(1, 30)) : null,
                        ])
                        ->withDemoImage($demoImageNum)
                        ->create([
                            'alt_text' => $data['title'].' featured image',
                        ]);
                    $photoId = $photo->id;
                    $imageCounter++;
                }
            }

            $article = Article::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'summary' => $data['summary'] ?? null,
                    'status' => $status,
                    'published_at' => $status === 'published' ? now()->subDays(random_int(1, 30)) : null,
                    'photo_id' => $photoId,
                    'meta' => $meta ?: null,
                ]
            );

            // Attach categories if not already attached
            if ($article->categories()->count() === 0 && isset($data['categories'])) {
                $categoryIds = Category::whereIn('name', $data['categories'])->pluck('id');
                $article->categories()->attach($categoryIds);
            }
        }
    }

    private function isExternalUrl(?string $url): bool
    {
        return $url !== null && str_starts_with($url, 'http');
    }
}

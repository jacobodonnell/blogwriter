<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use Database\Seeders\Concerns\ChecksExternalUrls;
use Illuminate\Database\Seeder;

final class FullArticleSeeder extends Seeder
{
    use ChecksExternalUrls;

    /**
     * Seed full articles (15 articles from JSON: 8 published, 7 draft after hidden->draft conversion).
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/test-data/full/articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        $user = User::firstOrFail();

        // Demo image counter for cycling through 4 demo images
        $demoImages = [1, 2, 3, 4];
        $imageCounter = 0;

        foreach ($articles as $data) {
            // Convert hidden status to draft (hidden status removed in refactoring)
            $status = match ($data['status']) {
                'published' => Status::Published,
                default => Status::Draft,
            };

            // Handle photo creation — only local demo images, external URLs go to column
            $photoId = null;
            $externalUrl = null;
            $meta = $data['meta'] ?? [];

            if ($data['featured_image'] !== null) {
                if ($this->isExternalUrl($data['featured_image'])) {
                    // Store external URL in dedicated column instead of creating a Photo
                    $externalUrl = $data['featured_image'];
                } else {
                    // Use local demo images
                    $demoImageNum = $demoImages[$imageCounter % count($demoImages)];
                    $photo = Photo::factory()
                        ->state([
                            'user_id' => $user->id,
                            'status' => $status,
                            'published_at' => $status === Status::Published ? now()->subDays(random_int(1, 30)) : null,
                        ])
                        ->withDemoImage($demoImageNum)
                        ->create([
                            'alt_text' => $data['title'].' featured image',
                        ]);
                    $photoId = $photo->id;
                    $imageCounter++;
                }
            }

            // Use the first category as the single category
            $categoryId = null;
            if (isset($data['categories']) && count($data['categories']) > 0) {
                $categoryId = Category::where('name', $data['categories'][0])->value('id');
            }

            Article::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'summary' => $data['summary'] ?? null,
                    'status' => $status,
                    'published_at' => $status === Status::Published ? now()->subDays(random_int(1, 30)) : null,
                    'photo_id' => $photoId,
                    'external_featured_img_url' => $externalUrl,
                    'meta' => $meta ?: null,
                    'category_id' => $categoryId,
                ]
            );
        }
    }
}

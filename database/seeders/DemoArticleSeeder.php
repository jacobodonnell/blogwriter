<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use Database\Seeders\Concerns\AttachesDemoImages;
use Database\Seeders\Concerns\ChecksExternalUrls;
use Illuminate\Database\Seeder;

final class DemoArticleSeeder extends Seeder
{
    use AttachesDemoImages;
    use ChecksExternalUrls;

    /**
     * Seed demo articles (5 articles from JSON: 4 published, 1 draft).
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/test-data/demo/articles.json');
        $articles = json_decode(file_get_contents($jsonPath), true);

        $user = User::firstOrFail();

        // Demo image counter for cycling through 4 demo images
        $demoImages = [1, 2, 3, 4];
        $imageCounter = 0;

        foreach ($articles as $data) {
            $status = $data['status'] === 'published' ? Status::Published : Status::Draft;

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
                    $photo = Photo::create([
                        'user_id' => $user->id,
                        'filename' => 'demo-image-'.$demoImageNum.'.jpg',
                        'slug' => 'article-photo-'.($imageCounter + 1),
                        'caption' => null,
                        'alt_text' => $data['title'].' featured image',
                        'status' => $status,
                        'published_at' => $status === Status::Published ? now()->subDays(random_int(1, 30)) : null,
                        'taken_at' => null,
                        'meta' => [],
                    ]);
                    $this->attachDemoImage($photo, $demoImageNum);
                    $photoId = $photo->id;
                    $imageCounter++;
                }
            }

            $categoryId = empty($data['categories'])
                ? null
                : Category::where('name', $data['categories'][0])->value('id');

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

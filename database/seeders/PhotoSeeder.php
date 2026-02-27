<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Photo;
use App\Models\User;
use Database\Seeders\Concerns\AttachesDemoImages;
use Illuminate\Database\Seeder;

final class PhotoSeeder extends Seeder
{
    use AttachesDemoImages;

    /**
     * Seed demo photos (5 photos with demo images).
     */
    public function run(): void
    {
        $user = User::firstOrFail();

        $photos = [
            1 => [
                'caption' => 'Illustration by: [HJ Project](https://unsplash.com/@hjproject/illustrations)',
                'alt_text' => 'Cartoon Anthropomorphic Food and Drinks',
            ],
            2 => [
                'caption' => 'Illustration by: [Rezza Alam](https://unsplash.com/@rezzaalam/illustrations)',
                'alt_text' => 'Illustration of boy playing video game on old console',
            ],
            3 => [
                'caption' => 'Illustration by: [Bilicube Studio](https://unsplash.com/@bilicubestudio/illustrations)',
                'alt_text' => 'Dog looking back over its shoulder',
            ],
            4 => [
                'caption' => 'Illustration by: [Cecilia Miraldi](https://unsplash.com/@ceciliamiraldi)',
                'alt_text' => 'Girl flying through the sky with a magic umbrella',
            ],
        ];

        foreach ($photos as $index => $metadata) {
            $photo = Photo::create([
                'user_id' => $user->id,
                'filename' => 'demo-image-'.$index.'.jpg',
                'slug' => 'demo-photo-'.$index,
                'caption' => $metadata['caption'],
                'alt_text' => $metadata['alt_text'],
                'status' => Status::Public,
                'published_at' => now(),
                'taken_at' => null,
                'meta' => [],
            ]);
            $this->attachDemoImage($photo, $index);
        }
    }
}

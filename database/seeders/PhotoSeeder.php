<?php

namespace Database\Seeders;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Seeder;

class PhotoSeeder extends Seeder
{
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
                'alt_text' => 'Demo image 3',
            ],
            4 => [
                'caption' => 'Illustration by: [Cecilia Miraldi](https://unsplash.com/@ceciliamiraldi)',
                'alt_text' => 'Girl flying through the sky with a magic umbrella',
            ],
            5 => [
                'caption' => 'Illustration by: [ål nik](https://unsplash.com/@aaaalnik)',
                'alt_text' => 'Silhouette of a woman with the words "My Body My Choice"',
            ],
        ];

        foreach ($photos as $index => $metadata) {
            Photo::factory()
                ->state(['user_id' => $user->id])
                ->published()
                ->withDemoImage($index)
                ->create($metadata);
        }
    }
}

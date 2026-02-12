<?php

namespace Database\Seeders;

use App\Models\Photo;
use Illuminate\Database\Seeder;

class PhotoSeeder extends Seeder
{
    /**
     * Seed demo photos (5 photos with demo images).
     */
    public function run(): void
    {
        // Create 5 demo photos
        for ($i = 1; $i <= 5; $i++) {
            Photo::factory()
                ->published()
                ->withDemoImage($i)
                ->create([
                    'caption' => 'Demo photo '.$i,
                    'alt_text' => 'Demo image '.$i,
                ]);
        }
    }
}

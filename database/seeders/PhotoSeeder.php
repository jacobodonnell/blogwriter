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

        // Create 5 demo photos
        for ($i = 1; $i <= 5; $i++) {
            Photo::factory()
                ->state(['user_id' => $user->id])
                ->published()
                ->withDemoImage($i)
                ->create([
                    'caption' => 'Demo photo '.$i,
                    'alt_text' => 'Demo image '.$i,
                ]);
        }
    }
}

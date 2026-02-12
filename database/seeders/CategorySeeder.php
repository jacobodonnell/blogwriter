<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General musings and observations',
            ],
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Tech news, gadgets, and digital culture',
            ],
            [
                'name' => 'Satire',
                'slug' => 'satire',
                'description' => 'Satirical takes on modern life',
            ],
            [
                'name' => 'Startups',
                'slug' => 'startups',
                'description' => 'The wild world of startup culture',
            ],
            [
                'name' => 'Programming',
                'slug' => 'programming',
                'description' => 'Code, developers, and software engineering',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }
}

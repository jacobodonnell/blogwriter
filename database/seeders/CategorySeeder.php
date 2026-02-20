<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    /**
     * Seed categories with hierarchy.
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
                'children' => [
                    [
                        'name' => 'PHP',
                        'slug' => 'php',
                        'description' => 'PHP language and ecosystem',
                    ],
                    [
                        'name' => 'JavaScript',
                        'slug' => 'javascript',
                        'description' => 'JavaScript, TypeScript, and the frontend world',
                    ],
                    [
                        'name' => 'Laravel',
                        'slug' => 'laravel',
                        'description' => 'The Laravel framework',
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );

            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                Category::firstOrCreate(
                    ['slug' => $childData['slug']],
                    $childData
                );
            }
        }
    }
}

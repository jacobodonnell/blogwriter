<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Blog category names for realistic data.
     *
     * @var array<string>
     */
    protected array $categoryNames = [
        'Technology',
        'Life',
        'Travel',
        'Tutorial',
        'Opinion',
        'Programming',
        'Design',
        'Productivity',
        'Writing',
        'Photography',
        'Food',
        'Books',
        'Music',
        'Movies',
        'Health',
        'Fitness',
        'Business',
        'Entrepreneurship',
        'Personal Development',
        'Career',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = \Illuminate\Support\Arr::random($this->categoryNames);

        return [
            'name' => $name,
            'description' => fake()->optional(0.7)->sentence(10),
        ];
    }

    /**
     * Create a child category under the given parent.
     */
    public function withParent(Category $parent): static
    {
        return $this->state(fn (): array => [
            'parent_id' => $parent->id,
        ]);
    }
}

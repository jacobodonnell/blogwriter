<?php

namespace Database\Factories;

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
        $name = $this->faker->randomElement($this->categoryNames);

        return [
            'name' => $name,
            'description' => $this->faker->optional(0.7)->sentence(10),
        ];
    }
}

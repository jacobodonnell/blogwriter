<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Category;
use App\Models\User;
use Database\Factories\Concerns\AttachesFeaturedImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
final class PhotoFactory extends Factory
{
    use AttachesFeaturedImages;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::first()?->id ?? User::factory(),
            'filename' => fake()->word().'.jpg',
            'slug' => fake()->unique()->slug(),
            'caption' => fake()->optional(0.7)->paragraph(),
            'alt_text' => fake()->sentence(),
            'status' => Status::Published,
            'published_at' => now(),
            'taken_at' => fake()->optional(0.5)->dateTimeBetween('-1 year'),
            'meta' => [],
        ];
    }

    /**
     * Indicate that the photo is published.
     */
    public function published(): static
    {
        return $this->state(['status' => Status::Published, 'published_at' => now()]);
    }

    /**
     * Indicate that the photo is a draft.
     */
    public function draft(): static
    {
        return $this->state(['status' => Status::Draft, 'published_at' => null]);
    }

    /**
     * Assign a category to the photo.
     */
    public function withCategory(Category $category): static
    {
        return $this->state(['category_id' => $category->id]);
    }

    /**
     * Attach a demo image after creating the photo.
     */
    public function withDemoImage(int $imageNumber): static
    {
        return $this->afterCreating(function (\App\Models\Photo $photo) use ($imageNumber): void {
            $this->attachDemoImage($photo, $imageNumber, 'image');
        });
    }
}

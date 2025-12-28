<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        $status = fake()->randomElement([
            ArticleStatus::Published,
            ArticleStatus::Published,
            ArticleStatus::Published,
            ArticleStatus::Published,
            ArticleStatus::Draft
        ]);

        return [
            'user_id' => User::first()->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '<p>' . implode('</p><p>', fake()->paragraphs(5)) . '</p>',
            'status' => $status,
            'published_at' => $status === ArticleStatus::Published
                ? fake()->dateTimeBetween('-1 year')
                : null
        ];
    }
}

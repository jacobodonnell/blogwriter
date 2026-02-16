<?php

use App\Models\Article;
use App\Models\Category;

it('displays published articles for a valid category slug', function (): void {
    $category = Category::factory()->create(['name' => 'Technology']);
    $article = Article::factory()->published()->create();
    $article->categories()->attach($category);

    $this->get(route('category.show', $category->slug))
        ->assertSuccessful()
        ->assertSee($article->title);
});

it('returns 404 for non-existent category slug', function (): void {
    $this->get(route('category.show', 'non-existent-slug'))
        ->assertNotFound();
});

it('does not show draft articles', function (): void {
    $category = Category::factory()->create(['name' => 'Travel']);
    $draft = Article::factory()->draft()->create();
    $draft->categories()->attach($category);

    $this->get(route('category.show', $category->slug))
        ->assertSuccessful()
        ->assertDontSee($draft->title);
});

it('paginates at 10 per page', function (): void {
    $category = Category::factory()->create(['name' => 'Design']);

    $articles = Article::factory()->published()->count(12)->create();
    $articles->each(fn ($article) => $article->categories()->attach($category));

    $response = $this->get(route('category.show', $category->slug));

    $response->assertSuccessful();
    $response->assertViewHas('articles', fn ($articles) => $articles->count() === 10);
});

it('orders articles by published_at descending', function (): void {
    $category = Category::factory()->create(['name' => 'Opinion']);

    $older = Article::factory()->published()->create([
        'title' => 'Older Article',
        'published_at' => now()->subDays(5),
    ]);
    $newer = Article::factory()->published()->create([
        'title' => 'Newer Article',
        'published_at' => now()->subDay(),
    ]);

    $older->categories()->attach($category);
    $newer->categories()->attach($category);

    $response = $this->get(route('category.show', $category->slug));

    $response->assertSuccessful();
    $response->assertViewHas('articles', function ($articles) {
        return $articles->first()->title === 'Newer Article'
            && $articles->last()->title === 'Older Article';
    });
});

it('handles empty category with no articles', function (): void {
    $category = Category::factory()->create(['name' => 'Music']);

    $this->get(route('category.show', $category->slug))
        ->assertSuccessful()
        ->assertViewHas('articles', fn ($articles) => $articles->isEmpty());
});

it('eager loads categories to prevent N+1', function (): void {
    $category = Category::factory()->create(['name' => 'Programming']);
    $article = Article::factory()->published()->create();
    $article->categories()->attach($category);

    $response = $this->get(route('category.show', $category->slug));

    $response->assertSuccessful();
    $response->assertViewHas('articles', function ($articles) {
        return $articles->first()->relationLoaded('categories');
    });
});

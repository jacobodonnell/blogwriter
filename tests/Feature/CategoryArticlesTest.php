<?php

use App\Models\Article;
use App\Models\Category;

it('displays published articles for a valid category slug', function (): void {
    $category = Category::factory()->create(['name' => 'Technology']);
    Article::factory()->published()->create(['category_id' => $category->id]);

    $this->get(route('category.show', $category->slug))
        ->assertSuccessful()
        ->assertSee($category->name);
});

it('returns 404 for non-existent category slug', function (): void {
    $this->get(route('category.show', 'non-existent-slug'))
        ->assertNotFound();
});

it('does not show draft articles', function (): void {
    $category = Category::factory()->create(['name' => 'Travel']);
    $draft = Article::factory()->draft()->create(['category_id' => $category->id]);

    $this->get(route('category.show', $category->slug))
        ->assertSuccessful()
        ->assertDontSee($draft->title);
});

it('paginates at 10 per page', function (): void {
    $category = Category::factory()->create(['name' => 'Design']);

    Article::factory()->published()->count(12)->create(['category_id' => $category->id]);

    $response = $this->get(route('category.show', $category->slug));

    $response->assertSuccessful();
    $response->assertViewHas('articles', fn ($articles) => $articles->count() === 10);
});

it('orders articles by published_at descending', function (): void {
    $category = Category::factory()->create(['name' => 'Opinion']);

    $older = Article::factory()->published()->create([
        'title' => 'Older Article',
        'published_at' => now()->subDays(5),
        'category_id' => $category->id,
    ]);
    $newer = Article::factory()->published()->create([
        'title' => 'Newer Article',
        'published_at' => now()->subDay(),
        'category_id' => $category->id,
    ]);

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

it('eager loads category to prevent N+1', function (): void {
    $category = Category::factory()->create(['name' => 'Programming']);
    Article::factory()->published()->create(['category_id' => $category->id]);

    $response = $this->get(route('category.show', $category->slug));

    $response->assertSuccessful();
    $response->assertViewHas('articles', function ($articles) {
        return $articles->first()->relationLoaded('category');
    });
});

it('includes subcategory articles in parent category page', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'PHP']);

    $parentArticle = Article::factory()->published()->create([
        'title' => 'Parent Article',
        'category_id' => $parent->id,
    ]);
    $childArticle = Article::factory()->published()->create([
        'title' => 'Child Article',
        'category_id' => $child->id,
    ]);

    $response = $this->get(route('category.show', $parent->slug));

    $response->assertSuccessful();
    $response->assertSee('Parent Article');
    $response->assertSee('Child Article');
});

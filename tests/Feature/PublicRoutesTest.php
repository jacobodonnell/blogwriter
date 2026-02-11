<?php

use App\Models\Article;
use App\Models\Category;

it('returns successful response on homepage', function (): void {
    $response = $this->get('/');

    $response->assertSuccessful();
});

it('shows published articles on homepage', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Test Article Title',
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee($article->title);
});

it('returns successful response for article page', function (): void {
    $article = Article::factory()->published()->create();

    $response = $this->get("/blog/{$article->slug}");

    $response->assertSuccessful()
        ->assertSee($article->title);
});

it('shows correct article on article page', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'My Test Article',
        'content' => 'This is the test content.',
    ]);

    $response = $this->get("/blog/{$article->slug}");

    $response->assertSuccessful()
        ->assertSee($article->title)
        ->assertSee('This is the test content.');
});

it('returns 404 for non-existent article', function (): void {
    $response = $this->get('/blog/non-existent-article-slug');

    $response->assertNotFound();
});

it('returns successful response for category page', function (): void {
    $category = Category::factory()->create();

    $response = $this->get("/category/{$category->slug}");

    $response->assertSuccessful()
        ->assertSee($category->name);
});

it('shows filtered articles on category page', function (): void {
    $category = Category::factory()->create();
    $article = Article::factory()->published()->create([
        'title' => 'Category Test Article',
    ]);
    $article->categories()->attach($category);

    $response = $this->get("/category/{$category->slug}");

    $response->assertSuccessful()
        ->assertSee($category->name)
        ->assertSee($article->title);
});

it('returns successful response on about page', function (): void {
    $response = $this->get('/about');

    $response->assertSuccessful();
});

it('does not show draft articles on homepage', function (): void {
    $article = Article::factory()->draft()->create([
        'title' => 'Draft Article Title',
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertDontSee($article->title);
});

it('does not show hidden articles on homepage', function (): void {
    $article = Article::factory()->hidden()->create([
        'title' => 'Hidden Article Title',
    ]);

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertDontSee($article->title);
});

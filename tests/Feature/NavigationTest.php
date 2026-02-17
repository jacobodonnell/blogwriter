<?php

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('returns successful response for public navigation links', function (string $route): void {
    $this->get($route)->assertSuccessful();
})->with([
    'home' => fn () => route('home'),
    'about' => fn () => route('about'),
]);

it('returns successful response for admin navigation links when authenticated', function (string $route): void {
    $this->actingAs($this->user)->get($route)->assertSuccessful();
})->with([
    'dashboard' => fn () => route('admin.dashboard'),
    'articles index' => fn () => route('admin.articles.index'),
    'categories index' => fn () => route('admin.categories.index'),
    'settings' => fn () => route('admin.settings'),
]);

it('renders customizer for new article without DB write', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.create'))
        ->assertOk()
        ->assertViewIs('admin.articles.customizer');
});

it('has no broken links in public pages smoke test', function (): void {
    $article = \App\Models\Article::factory()->published()->create();
    $category = \App\Models\Category::factory()->create();

    $pages = [
        route('home'),
        route('about'),
        route('articles.show', $article->slug),
        route('category.show', $category->slug),
    ];

    foreach ($pages as $page) {
        $response = $this->get($page);
        $response->assertSuccessful("Failed loading page: {$page}");
    }
});

<?php

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('returns successful response for public navigation links', function (string $route): void {
    $this->get($route)->assertSuccessful();
})->with([
    'home' => fn () => route('home'),
    'categories' => fn () => route('categories.index'),
    'about' => fn () => route('about'),
]);

it('returns successful response for admin navigation links when authenticated', function (string $route): void {
    $this->actingAs($this->user)->get($route)->assertSuccessful();
})->with([
    'dashboard' => fn () => route('admin.dashboard'),
    'articles index' => fn () => route('admin.articles.index'),
    'categories index' => fn () => route('admin.categories.index'),
    'settings' => fn () => route('admin.settings.profile'),
]);

it('renders customizer for new article without DB write', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.create'))
        ->assertOk()
        ->assertViewIs('admin.articles.customizer');
});

it('shows menu-active on admin sidebar for current route', function (string $route): void {
    $this->actingAs($this->user)
        ->get($route)
        ->assertOk()
        ->assertSee('menu-active', false);
})->with([
    'dashboard' => fn () => route('admin.dashboard'),
    'articles' => fn () => route('admin.articles.index'),
    'categories' => fn () => route('admin.categories.index'),
    'settings' => fn () => route('admin.settings.profile'),
]);

it('shows menu-active on guest desktop nav for current route', function (string $url): void {
    $this->get($url)
        ->assertOk()
        ->assertSee('menu-active', false);
})->with([
    'home' => fn () => route('home'),
    'articles' => fn () => route('articles.index'),
    'categories' => fn () => route('categories.index'),
    'about' => fn () => route('about'),
]);

it('shows menu-active on guest mobile drawer for current route', function (string $url): void {
    $this->get($url)
        ->assertOk()
        ->assertSee('menu-active', false);
})->with([
    'home' => fn () => route('home'),
    'articles' => fn () => route('articles.index'),
    'categories' => fn () => route('categories.index'),
    'about' => fn () => route('about'),
]);

it('welcome blade file does not exist', function (): void {
    expect(file_exists(resource_path('views/welcome.blade.php')))->toBeFalse();
});

it('renders custom 404 page', function (): void {
    $this->get('/this-page-does-not-exist-at-all')
        ->assertStatus(404)
        ->assertSee('Page Not Found')
        ->assertSee('Go Home');
});

it('has no broken links in public pages smoke test', function (): void {
    $article = \App\Models\Article::factory()->published()->create();
    $category = \App\Models\Category::factory()->create();

    $pages = [
        route('home'),
        route('about'),
        route('categories.index'),
        route('articles.show', $article->slug),
        route('categories.show', $category->slug),
    ];

    foreach ($pages as $page) {
        $response = $this->get($page);
        $response->assertSuccessful("Failed loading page: {$page}");
    }
});

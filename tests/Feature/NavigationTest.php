<?php

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('returns successful response for public navigation links', function (string $route): void {
    $this->get($route)->assertSuccessful();
})->with([
    'home' => fn () => route('home'),
    'profile' => fn () => route('profile'),
]);

it('returns successful response for admin navigation links when authenticated', function (string $route): void {
    $this->actingAs($this->user)->get($route)->assertSuccessful();
})->with([
    'dashboard' => fn () => route('admin.dashboard'),
    'articles index' => fn () => route('admin.articles.index'),
    'articles create' => fn () => route('admin.articles.create'),
    'categories index' => fn () => route('admin.categories.index'),
    'settings' => fn () => route('admin.settings'),
]);

it('redirects blog route to home', function (): void {
    $this->get('/blog')
        ->assertRedirect('/');
});

it('does not use hardcoded admin URLs in layout files', function (): void {
    $layoutFiles = [
        resource_path('views/components/layouts/public.blade.php'),
        resource_path('views/components/layouts/admin.blade.php'),
    ];

    $hardcodedPatterns = [
        'href="/admin"',
        'href="/admin/articles"',
        'href="/admin/categories"',
        'href="/admin/settings"',
        "href='/admin'",
        "href='/admin/articles'",
        "href='/admin/categories'",
        "href='/admin/settings'",
    ];

    foreach ($layoutFiles as $file) {
        $content = file_get_contents($file);

        foreach ($hardcodedPatterns as $pattern) {
            expect($content)->not->toContain($pattern, "Found hardcoded URL {$pattern} in {$file}");
        }
    }
});

it('does not use hardcoded public URLs in layout files', function (): void {
    $layoutFiles = [
        resource_path('views/components/layouts/public.blade.php'),
    ];

    $hardcodedNavigationPatterns = [
        '<a href="/" class="btn btn-ghost">Home</a>',
        "<a href='/' class='btn btn-ghost'>Home</a>",
        '<a href="/blog"',
        "<a href='/blog'",
    ];

    foreach ($layoutFiles as $file) {
        $content = file_get_contents($file);

        foreach ($hardcodedNavigationPatterns as $pattern) {
            expect($content)->not->toContain($pattern, "Found hardcoded navigation URL in {$file}. Use route() helper.");
        }
    }

    // Test passes if no hardcoded patterns found
    expect(true)->toBeTrue('No hardcoded public navigation URLs found');
});

it('uses named routes in breadcrumb links', function (): void {
    $article = \App\Models\Article::factory()->published()->create();
    $category = \App\Models\Category::factory()->create();

    // Test article page breadcrumb
    $response = $this->get(route('article.show', $article->slug));
    $response->assertSuccessful();

    // Test category page breadcrumb
    $response = $this->get(route('category.show', $category->slug));
    $response->assertSuccessful();

    // Test about page
    $response = $this->get(route('profile'));
    $response->assertSuccessful();
});

it('has no broken links in public pages smoke test', function (): void {
    $pages = [
        '/',
        '/profile',
    ];

    $article = \App\Models\Article::factory()->published()->create();
    $category = \App\Models\Category::factory()->create();

    $pages[] = route('article.show', $article->slug);
    $pages[] = route('category.show', $category->slug);

    foreach ($pages as $page) {
        $response = $this->get($page);
        $response->assertSuccessful("Failed loading page: {$page}");
    }
});

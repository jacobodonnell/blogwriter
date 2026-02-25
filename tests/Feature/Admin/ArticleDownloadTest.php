<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('downloads an article as a markdown file', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'my-article',
        'title' => 'My Article',
        'content' => 'Hello world.',
    ]);

    $response = $this->get(route('admin.articles.download', $article));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/markdown; charset=utf-8');
    expect($response->headers->get('Content-Disposition'))->toContain('my-article.md');
});

it('markdown download contains frontmatter and body', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'my-article',
        'title' => 'My Article',
        'content' => 'Hello world.',
    ]);

    $response = $this->get(route('admin.articles.download', $article));

    $content = $response->streamedContent();

    expect($content)
        ->toContain('title:')
        ->toContain('My Article')
        ->toContain('slug: my-article')
        ->toContain('Hello world.');
});

it('redirects unauthenticated users from the article download endpoint', function (): void {
    auth()->logout();

    $article = Article::factory()->published()->create();

    $this->get(route('admin.articles.download', $article))
        ->assertRedirect(route('login'));
});

it('returns 404 for a non-existent article download', function (): void {
    $this->get(route('admin.articles.download', 99999))
        ->assertNotFound();
});

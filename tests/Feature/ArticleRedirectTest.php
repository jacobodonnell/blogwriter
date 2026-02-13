<?php

use App\Models\Article;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('shows a published article at its current slug', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'my-article',
    ]);

    $this->get('/blog/my-article')
        ->assertOk();
});

it('301 redirects from an old slug to the current slug', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'new-slug',
        'past_slugs' => ['old-slug'],
    ]);

    $this->get('/blog/old-slug')
        ->assertRedirect('/blog/new-slug')
        ->assertStatus(301);
});

it('returns 404 for a nonexistent slug', function (): void {
    $this->get('/blog/does-not-exist')
        ->assertNotFound();
});

it('returns 404 for a past slug of a draft article', function (): void {
    $article = Article::factory()->draft()->create([
        'slug' => 'new-slug',
        'past_slugs' => ['old-slug'],
        'published_at' => null,
    ]);

    $this->get('/blog/old-slug')
        ->assertNotFound();
});

it('redirects to the latest slug when article has multiple past slugs', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'final-slug',
        'past_slugs' => ['first-slug', 'second-slug'],
    ]);

    $this->get('/blog/first-slug')
        ->assertRedirect('/blog/final-slug')
        ->assertStatus(301);

    $this->get('/blog/second-slug')
        ->assertRedirect('/blog/final-slug')
        ->assertStatus(301);
});

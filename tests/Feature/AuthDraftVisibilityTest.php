<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

// --- Articles Index ---

it('shows draft articles to authenticated users via status=draft', function (): void {
    $user = User::factory()->create();
    $draft = Article::factory()->draft()->create();

    $this->actingAs($user)
        ->get('/articles?status=draft')
        ->assertSuccessful()
        ->assertSee($draft->title);
});

it('hides draft articles from guests on articles index', function (): void {
    $draft = Article::factory()->draft()->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertDontSee($draft->title);
});

// --- Article Show ---

it('returns 404 for draft articles regardless of auth', function (): void {
    $user = User::factory()->create();
    $draft = Article::factory()->draft()->create(['slug' => 'draft-article']);

    $this->actingAs($user)
        ->get('/articles/draft-article')
        ->assertNotFound();
});

it('returns 404 for guests viewing draft articles', function (): void {
    Article::factory()->draft()->create(['slug' => 'draft-article']);

    $this->get('/articles/draft-article')
        ->assertNotFound();
});

// --- Photos Index ---

it('shows draft photos to authenticated users via status=draft', function (): void {
    $user = User::factory()->create();
    $draft = Photo::factory()->draft()->create(['alt_text' => 'Draft Photo Alt']);

    $this->actingAs($user)
        ->get('/photos?status=draft')
        ->assertSuccessful()
        ->assertSee('Draft Photo Alt');
});

it('hides draft photos from guests on photos index', function (): void {
    $draft = Photo::factory()->draft()->create(['alt_text' => 'Draft Photo Alt']);

    $this->get('/photos')
        ->assertSuccessful()
        ->assertDontSee('Draft Photo Alt');
});

// --- Photo Show ---

it('allows authenticated users to view draft photos', function (): void {
    $user = User::factory()->create();
    $draft = Photo::factory()->draft()->create(['slug' => 'draft-photo']);

    $this->actingAs($user)
        ->get('/photos/draft-photo')
        ->assertSuccessful();
});

it('returns 404 for guests viewing draft photos', function (): void {
    Photo::factory()->draft()->create(['slug' => 'draft-photo']);

    $this->get('/photos/draft-photo')
        ->assertNotFound();
});

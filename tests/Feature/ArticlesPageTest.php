<?php

use App\Models\Article;
use App\Models\User;

it('renders the articles page', function (): void {
    $this->get('/articles')
        ->assertSuccessful()
        ->assertViewIs('public.articles');
});

it('shows published articles', function (): void {
    $article = Article::factory()->published()->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertSee($article->title);
});

it('does not show draft articles', function (): void {
    $article = Article::factory()->draft()->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertDontSee($article->title);
});

it('paginates articles', function (): void {
    Article::factory()->published()->count(15)->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 10);
});

it('shows admin buttons for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/articles')
        ->assertSuccessful()
        ->assertSee('Manage')
        ->assertSee('New Article');
});

it('hides admin buttons for guests', function (): void {
    $this->get('/articles')
        ->assertSuccessful()
        ->assertDontSee('Manage')
        ->assertDontSee('New Article');
});

it('shows edit button on articles for authenticated users', function (): void {
    $user = User::factory()->create();
    Article::factory()->published()->create();

    $this->actingAs($user)
        ->get('/articles')
        ->assertSuccessful()
        ->assertSee('Edit');
});

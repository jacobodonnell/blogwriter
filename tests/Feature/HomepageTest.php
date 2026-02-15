<?php

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

it('renders the homepage', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertViewIs('public.index');
});

it('shows published articles on the homepage', function (): void {
    $article = Article::factory()->published()->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee($article->title);
});

it('does not show draft articles on the homepage', function (): void {
    $article = Article::factory()->draft()->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee($article->title);
});

it('shows photos in the sidebar', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee($photo->alt_text);
});

it('shows admin buttons for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertSuccessful()
        ->assertSee('New Article')
        ->assertSee('View All');
});

it('hides admin buttons for guests', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('New Article');
});

it('shows view all articles link for all users', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('View All');
});

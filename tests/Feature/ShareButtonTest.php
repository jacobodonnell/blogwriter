<?php

declare(strict_types=1);

use App\Models\Article;

it('renders bluesky share button in share-buttons component', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('Share on Bluesky')
        ->assertSee('bsky.app/intent/compose');
});

it('renders twitter share button in share-buttons component', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('Share on Twitter')
        ->assertSee('twitter.com/intent/tweet');
});

it('renders copy link button in share-buttons component', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('Copy Link');
});

<?php

use App\Models\Article;

it('renders bluesky share button in share-buttons component', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('Share on Bluesky')
        ->assertSee('bsky.app/intent/compose');
});

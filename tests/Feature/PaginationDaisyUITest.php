<?php

declare(strict_types=1);

use App\Models\Article;

it('renders pagination links when items exceed page size', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('class="join"', false);
});

it('renders previous and next navigation', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('Previous', false)
        ->assertSee('Next', false);
});

it('renders page number links', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('page=2', false);
});

it('does not render pagination without enough items', function (): void {
    Article::factory()->published()->count(5)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertDontSee('class="join"', false);
});

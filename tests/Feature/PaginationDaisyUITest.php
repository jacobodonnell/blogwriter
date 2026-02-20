<?php

declare(strict_types=1);

use App\Models\Article;

it('renders pagination with DaisyUI join and btn classes', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('class="join"', false)
        ->assertSee('btn btn-ghost join-item btn-sm', false)
        ->assertSee('btn btn-active join-item btn-sm', false);
});

it('renders disabled previous button on first page', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('btn btn-ghost btn-disabled join-item btn-sm', false);
});

it('renders mobile pagination with DaisyUI btn classes', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('btn btn-outline btn-sm btn-disabled', false)
        ->assertSee('btn btn-outline btn-sm', false);
});

it('renders results text with DaisyUI semantic color', function (): void {
    Article::factory()->published()->count(15)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertSee('text-base-content/70', false);
});

it('does not render pagination without enough items', function (): void {
    Article::factory()->published()->count(5)->create();

    $response = $this->get('/articles');

    $response->assertSuccessful()
        ->assertDontSee('class="join"', false);
});

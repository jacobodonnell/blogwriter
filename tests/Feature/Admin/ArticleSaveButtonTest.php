<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders view live link for published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('View Live');
});

it('does not render view live link for draft article', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertDontSee('View Live');
});

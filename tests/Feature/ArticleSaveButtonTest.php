<?php

use App\Models\Article;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders save draft button for draft article', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('Save Draft');
});

it('renders save changes button for published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('Save Changes');
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

it('renders save draft button for new article', function (): void {
    get(route('admin.articles.create'))
        ->assertOk()
        ->assertSee('Save Draft');
});

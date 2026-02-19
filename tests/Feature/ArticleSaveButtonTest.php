<?php

use App\Models\Article;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders save button with draft status config for draft article', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('articleCustomizer(')
        ->assertSee("initialStatus: 'draft'", false)
        ->assertSee('data-test="save-button"', false);
});

it('renders save button with published status config for published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('articleCustomizer(')
        ->assertSee("initialStatus: 'published'", false)
        ->assertSee('data-test="save-button"', false);
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

it('renders save button with draft status config for new article', function (): void {
    get(route('admin.articles.create'))
        ->assertOk()
        ->assertSee('articleCustomizer(')
        ->assertSee("initialStatus: 'draft'", false)
        ->assertSee('data-test="save-button"', false);
});

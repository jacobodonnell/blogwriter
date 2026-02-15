<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('displays customizer layout for edit page', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertViewIs('admin.articles.customizer');
});

it('shows full-page preview for draft articles', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.show', $article))
        ->assertOk()
        ->assertViewIs('admin.articles.preview-fullscreen')
        ->assertSee('Preview Mode');
});

it('shows full-page preview for published articles', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.show', $article))
        ->assertOk()
        ->assertViewIs('admin.articles.preview-fullscreen')
        ->assertSee('Preview Mode');
});

it('requires auth for preview', function (): void {
    auth()->logout();

    $article = Article::factory()->draft()->create();

    get(route('admin.articles.show', $article))
        ->assertRedirect();
});

it('returns preview partial for ajax preview update', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Original Title',
        'content' => 'Original content',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content here',
        'status' => 'draft',
    ])
        ->assertOk()
        ->assertViewIs('admin.articles.preview')
        ->assertSee('Updated Title');
});

it('redirects normally for full save update requests', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => 'draft',
    ])
        ->assertRedirect(route('admin.articles.edit', $article))
        ->assertSessionHas('success');
});

it('renders customizer for new article without persisting to database', function (): void {
    get(route('admin.articles.create'))
        ->assertOk()
        ->assertViewIs('admin.articles.customizer')
        ->assertViewHas('isNew', true);

    expect(Article::count())->toBe(0);
});

it('stores new article on first explicit save', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'My First Article',
        'slug' => 'my-first-article',
        'content' => 'Hello world',
        'status' => 'draft',
    ])->assertRedirect();

    $article = Article::first();

    expect($article)->not->toBeNull()
        ->and($article->title)->toBe('My First Article')
        ->and($article->slug)->toBe('my-first-article')
        ->and($article->status->value)->toBe('draft');
});

it('rejects update with empty content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => '',
        'status' => 'draft',
    ])->assertSessionHasErrors('content');
});

it('rejects update with missing content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'status' => 'draft',
    ])->assertSessionHasErrors('content');
});

it('preview update accepts relaxed validation', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Original',
        'slug' => 'untitled-abcd1234',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'Hi',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ])
        ->assertOk()
        ->assertViewIs('admin.articles.preview');
});

it('preview update auto-generates slug from title when placeholder', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Untitled Article',
        'slug' => 'untitled-abcd1234',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'My Great Post',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ])->assertOk();

    expect($article->fresh()->slug)->toBe('my-great-post');
});

it('preview update skips slug when it conflicts with another article', function (): void {
    Article::factory()->published()->for($this->user)->create([
        'slug' => 'there',
    ]);

    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Untitled Article',
        'slug' => 'untitled-abcd1234',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'There',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ])->assertOk();

    $article->refresh();
    // Slug should remain unchanged since "there" is taken
    expect($article->slug)->toBe('untitled-abcd1234')
        ->and($article->title)->toBe('There');
});

it('full save preserves existing featured image', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'meta' => ['featured_image_url' => 'https://example.com/image.jpg'],
    ]);

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => 'draft',
    ])->assertRedirect();

    expect($article->fresh()->meta['featured_image_url'])->toBe('https://example.com/image.jpg');
});

it('index view button links to preview for drafts', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee(route('admin.articles.show', $article))
        ->assertSee('Preview Draft');
});

it('index view button links to permalink for published', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee($article->permalink())
        ->assertSee('View Published');
});

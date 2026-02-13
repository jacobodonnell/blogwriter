<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('displays customizer layout for edit page', function (): void {
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = get(route('admin.articles.edit', $article));

    $response->assertOk();
    $response->assertViewIs('admin.articles.customizer');
});

it('shows full-page preview for draft articles', function (): void {
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = get(route('admin.articles.show', $article));

    $response->assertOk();
    $response->assertViewIs('admin.articles.preview-fullscreen');
    $response->assertSee('Preview Mode');
});

it('shows full-page preview for published articles', function (): void {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $response = get(route('admin.articles.show', $article));

    $response->assertOk();
    $response->assertViewIs('admin.articles.preview-fullscreen');
    $response->assertSee('Preview Mode');
});

it('requires auth for preview', function (): void {
    auth()->logout();

    $article = Article::factory()->draft()->create();

    $response = get(route('admin.articles.show', $article));

    $response->assertRedirect();
});

it('returns preview partial for ajax preview update', function (): void {
    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
        'title' => 'Original Title',
        'content' => 'Original content',
    ]);

    $response = put(route('admin.articles.preview.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content here',
        'status' => 'draft',
    ]);

    $response->assertOk();
    $response->assertViewIs('admin.articles.preview');
    $response->assertSee('Updated Title');
});

it('redirects normally for full save update requests', function (): void {
    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
    ]);

    $response = put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('admin.articles.edit', $article));
    $response->assertSessionHas('success');
});

it('creates draft immediately from create route', function (): void {
    $response = get(route('admin.articles.create'));

    $article = Article::latest()->first();

    expect($article)->not->toBeNull();
    expect($article->status->value)->toBe('draft');
    expect($article->title)->toBe('Untitled Article');

    $response->assertRedirect(route('admin.articles.edit', $article));
});

it('creates draft with lowercase placeholder slug', function (): void {
    get(route('admin.articles.create'));

    $article = Article::latest()->first();

    expect($article->slug)->toMatch('/^untitled-[a-z0-9]{8}$/');
});

it('preview update accepts relaxed validation', function (): void {
    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
        'title' => 'Original',
        'slug' => 'untitled-abcd1234',
    ]);

    $response = put(route('admin.articles.preview.update', $article), [
        'title' => 'Hi',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ]);

    $response->assertOk();
    $response->assertViewIs('admin.articles.preview');
});

it('preview update auto-generates slug from title when placeholder', function (): void {
    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
        'title' => 'Untitled Article',
        'slug' => 'untitled-abcd1234',
    ]);

    $response = put(route('admin.articles.preview.update', $article), [
        'title' => 'My Great Post',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ]);

    $response->assertOk();
    $article->refresh();
    expect($article->slug)->toBe('my-great-post');
});

it('preview update skips slug when it conflicts with another article', function (): void {
    Article::factory()->published()->create([
        'user_id' => $this->user->id,
        'slug' => 'there',
    ]);

    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
        'title' => 'Untitled Article',
        'slug' => 'untitled-abcd1234',
    ]);

    $response = put(route('admin.articles.preview.update', $article), [
        'title' => 'There',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ]);

    $response->assertOk();
    $article->refresh();
    // Slug should remain unchanged since "there" is taken
    expect($article->slug)->toBe('untitled-abcd1234');
    // But title should still update
    expect($article->title)->toBe('There');
});

it('full save preserves existing featured image', function (): void {
    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
        'meta' => ['featured_image_url' => 'https://example.com/image.jpg'],
    ]);

    $response = put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => 'draft',
    ]);

    $response->assertRedirect();

    $article->refresh();
    expect($article->meta['featured_image_url'])->toBe('https://example.com/image.jpg');
});

it('index view button links to preview for drafts', function (): void {
    $article = Article::factory()->draft()->create([
        'user_id' => $this->user->id,
        'published_at' => null,
    ]);

    $response = get(route('admin.articles.index'));

    $response->assertOk();
    $response->assertSee(route('admin.articles.show', $article));
    $response->assertSee('Preview Draft');
});

it('index view button links to permalink for published', function (): void {
    $article = Article::factory()->published()->create([
        'user_id' => $this->user->id,
    ]);

    $response = get(route('admin.articles.index'));

    $response->assertOk();
    $response->assertSee($article->permalink());
    $response->assertSee('View Published');
});

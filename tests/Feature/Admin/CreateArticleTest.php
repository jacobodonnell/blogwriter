<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders the customizer with isNew flag and no DB write', function (): void {
    get(route('admin.articles.create'))
        ->assertOk()
        ->assertViewIs('admin.articles.customizer')
        ->assertViewHas('isNew', true);

    expect(Article::count())->toBe(0);
});

it('preview stores data in session without persisting to database', function (): void {
    post(route('admin.articles.preview.store'), [
        'title' => 'Preview Title',
        'slug' => 'preview-title',
        'content' => 'Some preview content',
        'status' => 'draft',
    ])
        ->assertOk()
        ->assertViewIs('admin.articles.preview')
        ->assertSee('Preview Title');

    expect(Article::count())->toBe(0);
});

it('stores article on first explicit save and redirects to edit', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'My New Article',
        'slug' => 'my-new-article',
        'content' => 'Article content here',
        'summary' => 'A summary',
        'status' => 'draft',
    ])->assertRedirect();

    $article = Article::first();

    expect($article)->not->toBeNull()
        ->and($article->title)->toBe('My New Article')
        ->and($article->slug)->toBe('my-new-article')
        ->and($article->status->value)->toBe('draft')
        ->and($article->user_id)->toBe($this->user->id);
});

it('clears session draft after storing', function (): void {
    session()->put('draft_article', ['title' => 'Temp']);

    post(route('admin.articles.store'), [
        'title' => 'Saved Article',
        'slug' => 'saved-article',
        'content' => 'Content',
        'status' => 'draft',
    ])->assertRedirect();

    expect(session()->has('draft_article'))->toBeFalse();
});

it('assigns category when storing new article', function (): void {
    $category = Category::factory()->create();

    post(route('admin.articles.store'), [
        'title' => 'Categorized Article',
        'slug' => 'categorized-article',
        'content' => 'Content',
        'status' => 'draft',
        'category_id' => $category->id,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->category_id)->toBe($category->id)
        ->and($article->category->id)->toBe($category->id);
});

it('stores article with published status', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => 'Published content',
        'status' => 'published',
    ])->assertRedirect();

    $article = Article::first();

    expect($article->status->value)->toBe('published')
        ->and($article->published_at)->not->toBeNull();
});

it('preview auto-generates slug from title when placeholder', function (): void {
    post(route('admin.articles.preview.store'), [
        'title' => 'My Great Post',
        'slug' => 'untitled-abcd1234',
        'status' => 'draft',
    ])
        ->assertOk()
        ->assertSee('My Great Post');

    expect(Article::count())->toBe(0);
});

it('stores featured image URL in meta', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Article With Image',
        'slug' => 'article-with-image',
        'content' => 'Content',
        'status' => 'draft',
        'featured_image' => 'https://example.com/photo.jpg',
    ])->assertRedirect();

    $article = Article::first();

    expect($article->meta['featured_image_url'])->toBe('https://example.com/photo.jpg');
});

it('rejects storing article with empty content', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'No Content Article',
        'slug' => 'no-content-article',
        'content' => '',
        'status' => 'draft',
    ])->assertSessionHasErrors('content');

    expect(Article::count())->toBe(0);
});

it('rejects storing article with missing content', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'No Content Article',
        'slug' => 'no-content-article',
        'status' => 'draft',
    ])->assertSessionHasErrors('content');

    expect(Article::count())->toBe(0);
});

it('requires auth for create page', function (): void {
    auth()->logout();

    get(route('admin.articles.create'))
        ->assertRedirect();
});

it('requires auth for store', function (): void {
    auth()->logout();

    post(route('admin.articles.store'), [
        'title' => 'Unauthorized',
        'slug' => 'unauthorized',
        'content' => 'Content',
        'status' => 'draft',
    ])->assertRedirect();

    expect(Article::count())->toBe(0);
});

<?php

use App\Enums\Status;
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
        'status' => Status::Draft->value,
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
        'status' => Status::Draft->value,
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
        'status' => Status::Draft->value,
    ])->assertRedirect();

    expect(session()->has('draft_article'))->toBeFalse();
});

it('assigns category when storing new article', function (): void {
    $category = Category::factory()->create();

    post(route('admin.articles.store'), [
        'title' => 'Categorized Article',
        'slug' => 'categorized-article',
        'content' => 'Content',
        'status' => Status::Draft->value,
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
        'status' => Status::Published->value,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->status->value)->toBe('published')
        ->and($article->published_at)->not->toBeNull();
});

it('preview auto-generates slug from title when placeholder', function (): void {
    post(route('admin.articles.preview.store'), [
        'title' => 'My Great Post',
        'slug' => 'untitled-abcd1234',
        'status' => Status::Draft->value,
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
        'status' => Status::Draft->value,
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
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');

    expect(Article::count())->toBe(0);
});

it('rejects storing article with missing content', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'No Content Article',
        'slug' => 'no-content-article',
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');

    expect(Article::count())->toBe(0);
});

it('stores null summary when summary is empty', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'No Summary Article',
        'slug' => 'no-summary-article',
        'content' => 'Some content here',
        'summary' => '',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $article = Article::where('slug', 'no-summary-article')->first();

    expect($article)->not->toBeNull()
        ->and($article->getRawOriginal('summary'))->toBeNull();
});

it('stores null summary when summary is not provided', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Missing Summary Article',
        'slug' => 'missing-summary-article',
        'content' => 'Some content here',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $article = Article::where('slug', 'missing-summary-article')->first();

    expect($article)->not->toBeNull()
        ->and($article->getRawOriginal('summary'))->toBeNull();
});

it('excerpt accessor returns content fallback when summary is null', function (): void {
    $article = new Article;
    $article->setRawAttributes([
        'summary' => null,
        'content' => 'This is the article content for testing excerpts.',
    ]);

    expect($article->excerpt)->toContain('This is the article content');
});

it('preserves user-provided summary on save', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Has Summary',
        'slug' => 'has-summary',
        'content' => 'Some content',
        'summary' => 'My custom summary',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $article = Article::where('slug', 'has-summary')->first();

    expect($article->getRawOriginal('summary'))->toBe('My custom summary');
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
        'status' => Status::Draft->value,
    ])->assertRedirect();

    expect(Article::count())->toBe(0);
});

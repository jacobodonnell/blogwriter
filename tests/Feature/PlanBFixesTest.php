<?php

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

// --- 1. Admin <title> tags ---

it('renders admin articles index with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee('<title>Articles - '.config('app.name', 'BlogWriter').'</title>', false);
});

it('renders admin dashboard with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard - '.config('app.name', 'BlogWriter').'</title>', false);
});

// --- 2. welcome.blade.php deleted ---

it('welcome blade file does not exist', function (): void {
    expect(file_exists(resource_path('views/welcome.blade.php')))->toBeFalse();
});

// --- 3. Custom 404 page ---

it('renders custom 404 page', function (): void {
    $this->get('/this-page-does-not-exist-at-all')
        ->assertStatus(404)
        ->assertSee('Page Not Found')
        ->assertSee('Go Home');
});

// --- 4. Empty summary stores null ---

it('stores null summary when summary is empty', function (): void {
    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'No Summary Article',
            'slug' => 'no-summary-article',
            'content' => 'Some content here',
            'summary' => '',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $article = Article::where('slug', 'no-summary-article')->first();

    expect($article)->not->toBeNull()
        ->and($article->getRawOriginal('summary'))->toBeNull();
});

it('stores null summary when summary is not provided', function (): void {
    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Missing Summary Article',
            'slug' => 'missing-summary-article',
            'content' => 'Some content here',
            'status' => 'draft',
        ])
        ->assertRedirect();

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
    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Has Summary',
            'slug' => 'has-summary',
            'content' => 'Some content',
            'summary' => 'My custom summary',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $article = Article::where('slug', 'has-summary')->first();

    expect($article->getRawOriginal('summary'))->toBe('My custom summary');
});

// --- 5. Featured image alt text ---

it('returns title as fallback alt text', function (): void {
    $article = Article::factory()->create(['title' => 'Test Title']);

    expect($article->featured_image_alt)->toBe('Test Title');
});

it('returns meta alt text when set', function (): void {
    $article = Article::factory()->create([
        'meta' => ['featured_image_alt' => 'Custom alt text'],
    ]);

    expect($article->featured_image_alt)->toBe('Custom alt text');
});

it('returns photo alt text when photo has alt_text', function (): void {
    $photo = Photo::factory()->create(['alt_text' => 'Photo alt description']);
    $article = Article::factory()->create(['photo_id' => $photo->id]);

    expect($article->featured_image_alt)->toBe('Photo alt description');
});

it('prefers meta alt over photo alt text', function (): void {
    $photo = Photo::factory()->create(['alt_text' => 'Photo alt']);
    $article = Article::factory()->create([
        'photo_id' => $photo->id,
        'meta' => ['featured_image_alt' => 'Meta alt'],
    ]);

    expect($article->featured_image_alt)->toBe('Meta alt');
});

// --- 6. Bluesky share button ---

it('renders bluesky share button in share-buttons component', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('Share on Bluesky')
        ->assertSee('bsky.app/intent/compose');
});

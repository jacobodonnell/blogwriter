<?php

use App\Models\Article;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create(['name' => 'Test Author']);
});

// --- Article page: JSON-LD + OG tags + canonical ---

it('renders JSON-LD BlogPosting on article page', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'My Test Article',
        'content' => 'Some test content for the article body.',
        'meta' => [],
    ]);

    $this->get(route('articles.show', $article->slug))
        ->assertSuccessful()
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"BlogPosting"', false)
        ->assertSee('"headline":"My Test Article"', false)
        ->assertSee('"name":"Test Author"', false);
});

it('renders OG tags on article page', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'OG Test Article',
        'meta' => [],
    ]);

    $this->get(route('articles.show', $article->slug))
        ->assertSuccessful()
        ->assertSee('<meta property="og:title" content="OG Test Article">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('<meta name="twitter:card"', false);
});

it('renders canonical URL on article page', function (): void {
    $article = Article::factory()->published()->create();

    $response = $this->get(route('articles.show', $article->slug))
        ->assertSuccessful();

    $expectedUrl = route('articles.show', $article->slug);
    $response->assertSee('<link rel="canonical" href="'.$expectedUrl.'">', false);
});

it('uses custom meta overrides from article meta column', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Original Title',
        'meta' => [
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'Custom SEO description for search engines.',
        ],
    ]);

    $this->get(route('articles.show', $article->slug))
        ->assertSuccessful()
        ->assertSee('"headline":"Custom SEO Title"', false)
        ->assertSee('<meta property="og:title" content="Custom SEO Title">', false)
        ->assertSee('content="Custom SEO description for search engines."', false);
});

// --- Photo page: JSON-LD ImageObject ---

it('renders JSON-LD ImageObject on photo page', function (): void {
    $photo = Photo::factory()->published()->create([
        'alt_text' => 'A beautiful sunset',
    ]);

    $this->get(route('photos.show', $photo->slug))
        ->assertSuccessful()
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"ImageObject"', false)
        ->assertSee('"name":"A beautiful sunset"', false);
});

// --- Homepage: default meta tags ---

it('renders default meta tags on homepage', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<link rel="canonical"', false)
        ->assertSee('<meta name="twitter:card"', false);
});

// --- Placeholder image fallback ---

it('shows placeholder image in article cards when no featured image', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('blogwriter/placeholder.jpg', 'fake-image-data');
    Setting::set('site_placeholder_image', 'blogwriter/placeholder.jpg');

    Article::factory()->published()->create([
        'title' => 'No Image Article',
        'meta' => [],
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(Storage::disk('public')->url('blogwriter/placeholder.jpg'), false);
});

it('shows placeholder in articles listing when no featured image', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('blogwriter/placeholder.jpg', 'fake-image-data');
    Setting::set('site_placeholder_image', 'blogwriter/placeholder.jpg');

    Article::factory()->published()->create([
        'meta' => [],
    ]);

    $this->get(route('articles.index'))
        ->assertSuccessful()
        ->assertSee(Storage::disk('public')->url('blogwriter/placeholder.jpg'), false);
});

it('shows placeholder in admin articles table when no featured image', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('blogwriter/placeholder.jpg', 'fake-image-data');
    Setting::set('site_placeholder_image', 'blogwriter/placeholder.jpg');

    Article::factory()->published()->create([
        'meta' => [],
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.articles.index'))
        ->assertSuccessful()
        ->assertSee('opacity-40', false);
});

// --- Placeholder image upload ---

it('uploads a new placeholder image', function (): void {
    Storage::fake('public');

    $file = \Illuminate\Http\UploadedFile::fake()->image('new-placeholder.jpg', 400, 300);

    $this->actingAs($this->user)
        ->put(route('admin.settings.placeholder-image.update'), [
            'placeholder_image' => $file,
        ])
        ->assertRedirect(route('admin.settings'));

    expect(Setting::get('site_placeholder_image'))->not->toBeNull();
    Storage::disk('public')->assertExists(Setting::get('site_placeholder_image'));
});

it('rejects non-image files for placeholder upload', function (): void {
    Storage::fake('public');

    $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);

    $this->actingAs($this->user)
        ->put(route('admin.settings.placeholder-image.update'), [
            'placeholder_image' => $file,
        ])
        ->assertSessionHasErrors('placeholder_image');
});

// --- Fallback behavior ---

it('uses placeholder as OG image when article has no image', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('blogwriter/placeholder.jpg', 'fake-image-data');
    Setting::set('site_placeholder_image', 'blogwriter/placeholder.jpg');

    $article = Article::factory()->published()->create([
        'meta' => [],
    ]);

    $this->get(route('articles.show', $article->slug))
        ->assertSuccessful()
        ->assertSee('<meta property="og:image"', false);
});

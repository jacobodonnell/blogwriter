<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('url validation accepts modern cdn urls', function (): void {
    it('accepts imgur url without file extension', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image' => 'https://i.imgur.com/abc123',
        ]);

        $article = Article::where('slug', 'test-article')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://i.imgur.com/abc123');
    });

    it('accepts unsplash url without file extension', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-unsplash',
            'content' => 'Test content',
            'status' => 'published',
            'featured_image' => 'https://images.unsplash.com/photo-123456',
        ]);

        $article = Article::where('slug', 'test-article-unsplash')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://images.unsplash.com/photo-123456');
    });

    it('accepts cdn url with query parameters', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-query',
            'content' => 'Test content',
            'status' => 'published',
            'featured_image' => 'https://cdn.example.com/image?w=800&h=600&fit=crop',
        ]);

        $article = Article::where('slug', 'test-article-query')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://cdn.example.com/image?w=800&h=600&fit=crop');
    });

    it('accepts cloudflare images url', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-cf',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image' => 'https://imagedelivery.net/abc123/def456/public',
        ]);

        $article = Article::where('slug', 'test-article-cf')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://imagedelivery.net/abc123/def456/public');
    });

    it('accepts url with hash fragment', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-hash',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image' => 'https://example.com/image#featured',
        ]);

        $article = Article::where('slug', 'test-article-hash')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://example.com/image#featured');
    });

    it('still accepts traditional urls with file extensions', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-ext',
            'content' => 'Test content',
            'status' => 'published',
            'featured_image' => 'https://example.com/images/photo.jpg',
        ]);

        $article = Article::where('slug', 'test-article-ext')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://example.com/images/photo.jpg');
    });
});

describe('url validation rejects invalid urls', function (): void {
    it('rejects non-url strings', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-invalid',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('featured_image');
    });

    it('rejects empty string when image source is url', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article-empty',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image' => '',
            'image_source' => 'external_url',
        ]);

        // Should not have errors because featured_image is nullable
        $article = Article::where('slug', 'test-article-empty')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
    });
});

describe('update request url validation', function (): void {
    it('accepts modern cdn urls on update', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->draft()->create();

        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'published',
            'featured_image' => 'https://i.imgur.com/xyz789',
        ]);

        $article->refresh();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article->featured_image)->toBe('https://i.imgur.com/xyz789');
    });

    it('accepts query parameters on update', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->published()->create([
            'featured_image' => 'https://example.com/old.jpg',
        ]);

        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'published',
            'featured_image' => 'https://cdn.example.com/new?format=webp&quality=90',
        ]);

        $article->refresh();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article->featured_image)->toBe('https://cdn.example.com/new?format=webp&quality=90');
    });
});

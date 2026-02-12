<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Storage::fake('public');
    Storage::fake('private');
});

// ============================================================================
// IMAGE UPLOAD & WEBP CONVERSION TESTS
// ============================================================================

describe('image upload and webp conversion', function (): void {
    it('converts uploaded jpg image to webp format', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->getFirstMedia('featured_image'))->not->toBeNull();
        expect($article->featured_image_url)->not->toBeNull();
    });

    it('converts uploaded png image to webp format', function (): void {
        $file = UploadedFile::fake()->image('featured.png', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->getFirstMedia('featured_image'))->not->toBeNull();
        expect($article->featured_image_url)->not->toBeNull();
    });

    it('stores uploaded images as webp format', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();
        $media = $article->getFirstMedia('featured_image');

        expect($media->mime_type)->toBe('image/webp');
    });

    it('accepts webp files directly', function (): void {
        $file = UploadedFile::fake()->image('featured.webp', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->getFirstMedia('featured_image'))->not->toBeNull();
        expect($article->featured_image_url)->not->toBeNull();
    });
});

// ============================================================================
// MULTIPLE IMAGE SIZES GENERATION TESTS
// ============================================================================

describe('multiple image sizes generation', function (): void {
    it('generates all image conversion sizes', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();
        $media = $article->getFirstMedia('featured_image');

        expect($media)->not->toBeNull();
        expect($media->hasGeneratedConversion('thumbnail'))->toBeTrue();
        expect($media->hasGeneratedConversion('medium'))->toBeTrue();
        expect($media->hasGeneratedConversion('large'))->toBeTrue();
    });
});

// ============================================================================
// EDGE CASE TESTS
// ============================================================================

describe('edge cases', function (): void {
    it('handles square images correctly', function (): void {
        $file = UploadedFile::fake()->image('square.jpg', 500, 500);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->hasMedia('featured_image'))->toBeTrue();
    });

    it('rejects zero-byte image files', function (): void {
        $file = UploadedFile::fake()->create('empty.jpg', 0, 'image/jpeg');

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors('featured_image_file');
    });
});

// ============================================================================
// UPDATE & REPLACE TESTS
// ============================================================================

describe('update and replace', function (): void {
    it('replaces old image when uploading new one', function (): void {
        $oldFile = UploadedFile::fake()->image('old.jpg', 800, 600);
        $article = Article::factory()->create(['status' => 'published']);

        // Upload first image
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image_file' => $oldFile,
            ])
            ->assertRedirect();

        $article->refresh();
        $oldMediaId = $article->getFirstMedia('featured_image')->id;

        // Replace with new image
        $newFile = UploadedFile::fake()->image('new.jpg', 1200, 800);
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image_file' => $newFile,
            ])
            ->assertRedirect();

        $article->refresh();

        expect($article->hasMedia('featured_image'))->toBeTrue();
        expect($article->getFirstMedia('featured_image')->id)->not->toBe($oldMediaId);
    });

    it('removes featured image when delete is checked', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);
        $article = Article::factory()->create(['status' => 'published']);

        // Upload image first
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article->refresh();
        expect($article->hasMedia('featured_image'))->toBeTrue();

        // Remove image
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'remove_featured_image' => '1',
            ])
            ->assertRedirect();

        $article->refresh();

        expect($article->hasMedia('featured_image'))->toBeFalse();
    });

    it('handles external URL featured images', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image' => 'https://example.com/image.jpg',
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->toBe('https://example.com/image.jpg');
        expect($article->hasMedia('featured_image'))->toBeFalse();
    });
});

// ============================================================================
// INTEGRATION TESTS
// ============================================================================

describe('integration', function (): void {
    it('uploads images through controller store method', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ]);

        $response->assertRedirect();

        $article = Article::first();
        expect($article->hasMedia('featured_image'))->toBeTrue();
        expect($article->featured_image_url)->not->toBeNull();
    });

    it('uploads images through controller update method', function (): void {
        $article = Article::factory()->create(['status' => 'published']);
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $response = $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image_file' => $file,
            ]);

        $response->assertRedirect();

        $article->refresh();
        expect($article->hasMedia('featured_image'))->toBeTrue();
        expect($article->featured_image_url)->not->toBeNull();
    });
});

// ============================================================================
// PRIVACY & DISK HANDLING WITH PROCESSING
// ============================================================================

describe('privacy and disk handling with processing', function (): void {
    it('stores published article images on public disk', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();
        $media = $article->getFirstMedia('featured_image');

        expect($article->status->value)->toBe('published');
        expect($media)->not->toBeNull();
        expect($media->disk)->toBe('public');
    });

    it('stores draft article images on private disk', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();
        $media = $article->getFirstMedia('featured_image');

        expect($article->status->value)->toBe('draft');
        expect($media)->not->toBeNull();
        expect($media->disk)->toBe('private');
    });

    it('moves image to private disk when changing from published to draft', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 800, 600);
        $article = Article::factory()->create(['status' => 'published']);

        // Upload as published
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => 'published',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article->refresh();
        expect($article->getFirstMedia('featured_image')->disk)->toBe('public');

        // Change to draft
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => 'draft',
            ])
            ->assertRedirect();

        $article->refresh();
        $media = $article->getFirstMedia('featured_image');

        expect($article->status->value)->toBe('draft');
        expect($media->disk)->toBe('private');
    });
});

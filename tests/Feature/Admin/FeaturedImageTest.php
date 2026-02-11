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
// HAPPY PATH TESTS
// ============================================================================

describe('happy paths', function (): void {
    it('persists external URL to database', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => 'https://example.com/image.jpg',
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->toBe('https://example.com/image.jpg');
    });

    it('persists file upload to database and storage', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

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

        expect($article->featured_image)->not->toBeNull();
        // Draft articles store files on private disk
        Storage::disk('private')->assertExists($article->featured_image);
    });

    it('displays saved image on edit page', function (): void {
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/saved-image.jpg',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.edit', $article))
            ->assertSuccessful();

        $response->assertSee('https://example.com/saved-image.jpg');
    });

    it('removes featured image when checkbox is checked', function (): void {
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

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

        expect($article->featured_image)->toBeNull();
    });
});

// ============================================================================
// VALIDATION TESTS
// ============================================================================

describe('validation', function (): void {
    it('rejects invalid file types', function (): void {
        $file = UploadedFile::fake()->create('malware.exe', 100);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors('featured_image_file');
    });

    it('rejects oversized files', function (): void {
        $file = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors('featured_image_file');
    });

    it('rejects invalid URLs', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => 'not-a-valid-url',
            ])
            ->assertSessionHasErrors('featured_image');
    });

    it('rejects when both URL and file are provided', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => 'https://example.com/image.jpg',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors(['featured_image', 'featured_image_file']);
    });
});

// ============================================================================
// PRIVACY TESTS - STORAGE DISK SELECTION
// ============================================================================

describe('privacy and storage', function (): void {
    it('stores published article images on public disk', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

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

        expect($article->status->value)->toBe('published');
        Storage::disk('public')->assertExists($article->featured_image);
    });

    it('stores draft article images on private disk', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

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

        expect($article->status->value)->toBe('draft');
        Storage::disk('private')->assertExists($article->featured_image);
    });

    it('stores hidden article images on private disk', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'hidden',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->status->value)->toBe('hidden');
        Storage::disk('private')->assertExists($article->featured_image);
    });

    it('moves image from public to private when published becomes draft', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');
        $article = Article::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // First upload the image
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
        $originalPath = $article->featured_image;
        Storage::disk('public')->assertExists($originalPath);

        // Now change status to draft
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => 'draft',
            ])
            ->assertRedirect();

        $article->refresh();

        // Image should have moved to private disk
        expect($article->status->value)->toBe('draft');
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('private')->assertExists($article->featured_image);
    });

    it('moves image from private to public when draft becomes published', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');
        $article = Article::factory()->create([
            'status' => 'draft',
        ]);

        // First upload the image as draft
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
        $originalPath = $article->featured_image;
        Storage::disk('private')->assertExists($originalPath);

        // Now publish the article
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => 'published',
            ])
            ->assertRedirect();

        $article->refresh();

        // Image should have moved to public disk
        expect($article->status->value)->toBe('published');
        Storage::disk('private')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($article->featured_image);
    });
});

// ============================================================================
// EDGE CASES
// ============================================================================

describe('edge cases', function (): void {
    it('allows creating article without featured image', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->toBeNull();
    });

    it('enforces maximum URL length', function (): void {
        $longUrl = 'https://example.com/'.str_repeat('a', 500);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => $longUrl,
            ])
            ->assertSessionHasErrors('featured_image');
    });

    it('handles featured image update on existing article', function (): void {
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/old-image.jpg',
        ]);

        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image' => 'https://example.com/new-image.jpg',
            ])
            ->assertRedirect();

        $article->refresh();

        expect($article->featured_image)->toBe('https://example.com/new-image.jpg');
    });
});

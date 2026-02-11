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

        expect($article->featured_image)->toEndWith('.webp');
        Storage::disk('public')->assertExists($article->featured_image);
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

        expect($article->featured_image)->toEndWith('.webp');
        Storage::disk('public')->assertExists($article->featured_image);
    });

    it('deletes original file after webp conversion', function (): void {
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

        // Original JPG should not exist, only WebP
        expect($article->featured_image)->not->toContain('.jpg');
        expect($article->featured_image)->toEndWith('.webp');
    });

    it('does not re-convert already webp files', function (): void {
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

        expect($article->featured_image)->toEndWith('.webp');
        Storage::disk('public')->assertExists($article->featured_image);
    });
});

// ============================================================================
// MULTIPLE IMAGE SIZES GENERATION TESTS
// ============================================================================

describe('multiple image sizes generation', function (): void {
    it('generates thumbnail size of 150x150 pixels', function (): void {
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
        $thumbnailPath = str_replace('.webp', '-thumbnail.webp', $article->featured_image);

        Storage::disk('public')->assertExists($thumbnailPath);
    });

    it('generates medium size of 300x300 pixels', function (): void {
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
        $mediumPath = str_replace('.webp', '-medium.webp', $article->featured_image);

        Storage::disk('public')->assertExists($mediumPath);
    });

    it('generates large size of 1024x1024 pixels', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg', 1600, 1200);

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
        $largePath = str_replace('.webp', '-large.webp', $article->featured_image);

        Storage::disk('public')->assertExists($largePath);
    });

    it('generates all sizes in webp format', function (): void {
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
        $basePath = $article->featured_image;
        $thumbnailPath = str_replace('.webp', '-thumbnail.webp', $basePath);
        $mediumPath = str_replace('.webp', '-medium.webp', $basePath);
        $largePath = str_replace('.webp', '-large.webp', $basePath);

        expect($thumbnailPath)->toEndWith('.webp');
        expect($mediumPath)->toEndWith('.webp');
        expect($largePath)->toEndWith('.webp');
    });

    it('preserves aspect ratio in all generated sizes', function (): void {
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

        // Aspect ratio should be 4:3 (800/600 = 1.33)
        expect($article->featured_image_width)->toBe(800);
        expect($article->featured_image_height)->toBe(600);
    });
});

// ============================================================================
// IMAGE METADATA STORAGE TESTS
// ============================================================================

describe('image metadata storage', function (): void {
    it('stores image width in database', function (): void {
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

        expect($article->featured_image_width)->toBe(800);
    });

    it('stores image height in database', function (): void {
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

        expect($article->featured_image_height)->toBe(600);
    });

    it('stores image file size in database', function (): void {
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

        expect($article->featured_image_file_size)->toBeGreaterThan(0);
    });

    it('stores mime type as webp in database', function (): void {
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

        expect($article->featured_image_mime_type)->toBe('image/webp');
    });
});

// ============================================================================
// MAX DIMENSION LIMIT TESTS (WordPress-style)
// ============================================================================

describe('max dimension limits', function (): void {
    it('scales down images wider than 2560 pixels', function (): void {
        $file = UploadedFile::fake()->image('large.jpg', 3000, 2000);

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

        expect($article->featured_image_width)->toBeLessThanOrEqual(2560);
        expect($article->featured_image_height)->toBeLessThanOrEqual(2560);
    });

    it('scales down images taller than 2560 pixels', function (): void {
        $file = UploadedFile::fake()->image('tall.jpg', 2000, 3000);

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

        expect($article->featured_image_width)->toBeLessThanOrEqual(2560);
        expect($article->featured_image_height)->toBeLessThanOrEqual(2560);
    });

    it('preserves aspect ratio when scaling down large images', function (): void {
        $file = UploadedFile::fake()->image('large.jpg', 3000, 2000);

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

        // Original ratio: 3000/2000 = 1.5 (3:2)
        $ratio = $article->featured_image_width / $article->featured_image_height;
        expect($ratio)->toBeGreaterThan(1.49);
        expect($ratio)->toBeLessThan(1.51);
    });
});

// ============================================================================
// DATABASE SCHEMA TESTS
// ============================================================================

describe('database schema', function (): void {
    it('has featured_image_width column', function (): void {
        $article = Article::factory()->create();

        expect($article)->toHaveProperty('featured_image_width');
    });

    it('has featured_image_height column', function (): void {
        $article = Article::factory()->create();

        expect($article)->toHaveProperty('featured_image_height');
    });

    it('has featured_image_file_size column', function (): void {
        $article = Article::factory()->create();

        expect($article)->toHaveProperty('featured_image_file_size');
    });

    it('has featured_image_mime_type column', function (): void {
        $article = Article::factory()->create();

        expect($article)->toHaveProperty('featured_image_mime_type');
    });

    it('defaults metadata columns to null for new articles', function (): void {
        $article = Article::factory()->create([
            'featured_image' => null,
        ]);

        expect($article->featured_image_width)->toBeNull();
        expect($article->featured_image_height)->toBeNull();
        expect($article->featured_image_file_size)->toBeNull();
        expect($article->featured_image_mime_type)->toBeNull();
    });
});

// ============================================================================
// STORAGE & RETRIEVAL TESTS
// ============================================================================

describe('storage and retrieval', function (): void {
    it('stores generated images in articles/featured/{article_id}/ directory', function (): void {
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

        expect($article->featured_image)->toStartWith("articles/featured/{$article->id}/");
    });

    it('uses correct path pattern: articles/featured/{id}/{size}.webp', function (): void {
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
        $basePath = $article->featured_image;

        // Should match pattern: articles/featured/{id}/original.webp or similar
        expect($basePath)->toMatch('/^articles\/featured\/\d+\/[\w\-]+\.webp$/');
    });

    it('stores webp path in database not original format', function (): void {
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

        expect($article->featured_image)->toEndWith('.webp');
        expect($article->featured_image)->not->toContain('.jpg');
    });

    it('makes all image sizes accessible via storage facade', function (): void {
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
        $basePath = $article->featured_image;

        Storage::disk('public')->assertExists($basePath);
        Storage::disk('public')->assertExists(str_replace('.webp', '-thumbnail.webp', $basePath));
        Storage::disk('public')->assertExists(str_replace('.webp', '-medium.webp', $basePath));
        Storage::disk('public')->assertExists(str_replace('.webp', '-large.webp', $basePath));
    });
});

// ============================================================================
// EDGE CASE TESTS
// ============================================================================

describe('edge cases', function (): void {
    it('does not upsize very small images', function (): void {
        $file = UploadedFile::fake()->image('small.jpg', 50, 50);

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

        // Small image should stay 50x50, not be enlarged
        expect($article->featured_image_width)->toBe(50);
        expect($article->featured_image_height)->toBe(50);
    });

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

        expect($article->featured_image_width)->toBe(500);
        expect($article->featured_image_height)->toBe(500);

        // Aspect ratio should be 1:1
        $ratio = $article->featured_image_width / $article->featured_image_height;
        expect($ratio)->toBe(1.0);
    });

    it('preserves 16:9 aspect ratio for landscape images', function (): void {
        $file = UploadedFile::fake()->image('landscape.jpg', 1600, 900);

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

        // Original ratio: 1600/900 = 1.777... (16:9)
        $ratio = $article->featured_image_width / $article->featured_image_height;
        expect($ratio)->toBeGreaterThan(1.77);
        expect($ratio)->toBeLessThan(1.78);
    });

    it('preserves 9:16 aspect ratio for portrait images', function (): void {
        $file = UploadedFile::fake()->image('portrait.jpg', 900, 1600);

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

        // Original ratio: 900/1600 = 0.5625 (9:16)
        $ratio = $article->featured_image_width / $article->featured_image_height;
        expect($ratio)->toBeGreaterThan(0.56);
        expect($ratio)->toBeLessThan(0.57);
    });

    it('rejects corrupted image files', function (): void {
        $file = UploadedFile::fake()->create('corrupted.jpg', 100, 'image/jpeg');

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
    it('deletes old images when replacing with new upload', function (): void {
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
        $oldPath = $article->featured_image;
        Storage::disk('public')->assertExists($oldPath);

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

        // Old images should be deleted
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertMissing(str_replace('.webp', '-thumbnail.webp', $oldPath));
        Storage::disk('public')->assertMissing(str_replace('.webp', '-medium.webp', $oldPath));
        Storage::disk('public')->assertMissing(str_replace('.webp', '-large.webp', $oldPath));

        // New images should exist
        Storage::disk('public')->assertExists($article->featured_image);
    });

    it('clears metadata when removing featured image', function (): void {
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
        expect($article->featured_image_width)->not->toBeNull();

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

        expect($article->featured_image)->toBeNull();
        expect($article->featured_image_width)->toBeNull();
        expect($article->featured_image_height)->toBeNull();
        expect($article->featured_image_file_size)->toBeNull();
    });

    it('keeps metadata null for external urls', function (): void {
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
        expect($article->featured_image_width)->toBeNull();
        expect($article->featured_image_height)->toBeNull();
        expect($article->featured_image_file_size)->toBeNull();
    });
});

// ============================================================================
// INTEGRATION TESTS
// ============================================================================

describe('integration', function (): void {
    it('processes images when creating article via Article model', function (): void {
        Storage::fake('public');

        $article = Article::create([
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'status' => Status::Published,
            'featured_image' => 'articles/featured/test.webp', // Simulating pre-processed image
            'featured_image_width' => 800,
            'featured_image_height' => 600,
            'featured_image_file_size' => 50000,
            'featured_image_mime_type' => 'image/webp',
        ]);

        expect($article->featured_image_width)->toBe(800);
        expect($article->featured_image_height)->toBe(600);
    });

    it('processes images through controller store method', function (): void {
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
        expect($article->featured_image)->not->toBeNull();
        expect($article->featured_image)->toEndWith('.webp');
    });

    it('processes images through controller update method', function (): void {
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
        expect($article->featured_image)->not->toBeNull();
        expect($article->featured_image)->toEndWith('.webp');
    });

    it('handles image processing in factory published state', function (): void {
        Storage::fake('public');

        $article = Article::factory()->published()->create([
            'featured_image' => 'articles/featured/factory-test.webp',
            'featured_image_width' => 1200,
            'featured_image_height' => 800,
            'featured_image_file_size' => 75000,
        ]);

        expect($article->status)->toBe(Status::Published);
        expect($article->featured_image_width)->toBe(1200);
        expect($article->featured_image_height)->toBe(800);
    });
});

// ============================================================================
// PRIVACY & DISK HANDLING WITH PROCESSING
// ============================================================================

describe('privacy and disk handling with processing', function (): void {
    it('processes and stores published images on public disk', function (): void {
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

        expect($article->status->value)->toBe('published');
        Storage::disk('public')->assertExists($article->featured_image);
        Storage::disk('public')->assertExists(str_replace('.webp', '-thumbnail.webp', $article->featured_image));
        Storage::disk('private')->assertMissing($article->featured_image);
    });

    it('processes and stores draft images on private disk', function (): void {
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

        expect($article->status->value)->toBe('draft');
        Storage::disk('private')->assertExists($article->featured_image);
        Storage::disk('private')->assertExists(str_replace('.webp', '-thumbnail.webp', $article->featured_image));
        Storage::disk('public')->assertMissing($article->featured_image);
    });

    it('moves all image sizes when changing from public to private', function (): void {
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
        $originalPath = $article->featured_image;
        $thumbnailPath = str_replace('.webp', '-thumbnail.webp', $originalPath);
        $mediumPath = str_replace('.webp', '-medium.webp', $originalPath);
        $largePath = str_replace('.webp', '-large.webp', $originalPath);

        Storage::disk('public')->assertExists($originalPath);
        Storage::disk('public')->assertExists($thumbnailPath);

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
        $newPath = $article->featured_image;

        // All sizes should be moved to private
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertMissing($thumbnailPath);
        Storage::disk('public')->assertMissing($mediumPath);
        Storage::disk('public')->assertMissing($largePath);

        Storage::disk('private')->assertExists($newPath);
        Storage::disk('private')->assertExists(str_replace('.webp', '-thumbnail.webp', $newPath));
        Storage::disk('private')->assertExists(str_replace('.webp', '-medium.webp', $newPath));
        Storage::disk('private')->assertExists(str_replace('.webp', '-large.webp', $newPath));
    });
});

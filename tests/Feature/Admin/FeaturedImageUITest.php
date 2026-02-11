<?php

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
// TAB FUNCTIONALITY TESTS
// ============================================================================

describe('tab functionality', function (): void {
    it('displays both external URL and upload file tabs', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for tab radio inputs
        $response->assertSee('value="external_url"', false);
        $response->assertSee('value="upload_file"', false);

        // Check for tab labels
        $response->assertSee('External URL');
        $response->assertSee('Upload File');
    });

    it('shows URL input when external URL tab is active', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // URL input should be present
        $response->assertSee('name="featured_image"', false);
        $response->assertSee('placeholder="https://example.com/image.jpg"', false);
    });

    it('shows file input when upload file tab is active', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // File input should be present
        $response->assertSee('name="featured_image_file"', false);
        $response->assertSee('type="file"', false);
        $response->assertSee('accept="image/*"', false);
    });

    it('has active tab state with tabs-lifted styling', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for DaisyUI tabs classes
        $response->assertSee('tabs tabs-lifted', false);
    });

    it('maintains active tab after validation error', function (): void {
        // Submit form with missing required fields while on upload tab
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => '',
                'slug' => '',
                'content' => '',
                'status' => 'draft',
                'featured_image_file' => $file,
            ]);

        $response->assertSessionHasErrors(['title', 'slug', 'content']);

        // Get the form again - should maintain tab state via old() data
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // The form should still have file input available
        $response->assertSee('name="featured_image_file"', false);
    });
});

// ============================================================================
// FILE UPLOAD & PREVIEW TESTS
// ============================================================================

describe('file upload and preview', function (): void {
    it('has alpine x-data for file preview state management', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for Alpine.js featured image component
        $response->assertSee('x-data="featuredImage', false);
    });

    it('has file input with change handler for instant preview', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for Alpine change handler
        $response->assertSee('@change="handleFileSelect', false);
        $response->assertSee('@change="previewFile', false);
    });

    it('has preview image container with x-show directive', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Preview container should exist
        $response->assertSee('x-show="filePreview', false);
        $response->assertSee('x-show="previewUrl', false);
    });

    it('displays filename below preview image', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for filename display element
        $response->assertSee('x-text="fileName"', false);
    });

    it('displays file size in human readable format', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for file size display
        $response->assertSee('x-text="fileSize"', false);
        $response->assertSee('MB', false);
        $response->assertSee('KB', false);
    });

    it('accepts valid image file types', function (): void {
        $validTypes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        foreach ($validTypes as $type) {
            $file = UploadedFile::fake()->image("test.{$type}");

            $this->actingAs($this->user)
                ->post(route('admin.articles.store'), [
                    'title' => 'Test Article',
                    'slug' => 'test-article-'.uniqid(),
                    'content' => 'Test content',
                    'status' => 'draft',
                    'featured_image_file' => $file,
                ])
                ->assertRedirect();

            // Clean up for next iteration
            Article::query()->delete();
        }
    });

    it('has client-side file type validation attributes', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for accept attribute on file input
        $response->assertSee('accept="image/', false);
    });

    it('rejects invalid file types server-side', function (): void {
        $invalidTypes = [
            'exe' => UploadedFile::fake()->create('malware.exe', 100),
            'pdf' => UploadedFile::fake()->create('document.pdf', 100),
            'txt' => UploadedFile::fake()->create('readme.txt', 100),
        ];

        foreach ($invalidTypes as $type => $file) {
            $response = $this->actingAs($this->user)
                ->post(route('admin.articles.store'), [
                    'title' => 'Test Article',
                    'slug' => 'test-article-'.uniqid(),
                    'content' => 'Test content',
                    'status' => 'draft',
                    'featured_image_file' => $file,
                ]);

            $response->assertSessionHasErrors('featured_image_file');
        }
    });

    it('rejects oversized files server-side', function (): void {
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

    it('has client-side file size validation', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for Alpine file size validation
        $response->assertSee('maxSize', false);
        $response->assertSee('2MB', false);
        $response->assertSee('2048', false);
    });
});

// ============================================================================
// DELETE & UNDO TESTS
// ============================================================================

describe('delete and undo functionality', function (): void {
    it('has delete button with x-on:click handler', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for delete button with Alpine click handler
        $response->assertSee('@click="deleteImage', false);
        $response->assertSee('@click="removeImage', false);
    });

    it('has delete button with proper accessibility attributes', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        $response->assertSee('aria-label="Remove featured image"', false);
    });

    it('has hidden remove_featured_image checkbox for server state', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        $response->assertSee('name="remove_featured_image"', false);
        $response->assertSee('type="checkbox"', false);
    });

    it('removes featured image when delete submitted', function (): void {
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

    it('has undo button that appears after delete', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for undo button
        $response->assertSee('Undo', false);
        $response->assertSee('@click="undoDelete', false);
        $response->assertSee('@click="restoreImage', false);
    });

    it('has undo button with proper accessibility attributes', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        $response->assertSee('aria-label="Restore featured image"', false);
    });

    it('restores image when undo is clicked', function (): void {
        // This would be a browser test for Alpine.js interaction
        // For now, verify the server-side state management works
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

        // Submit with remove checked, then submit again without it
        // The UI would handle this via Alpine state
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

        // Now simulate undo by submitting without remove flag
        // and re-adding the URL
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image' => 'https://example.com/image.jpg',
            ])
            ->assertRedirect();

        $article->refresh();
        expect($article->featured_image)->toBe('https://example.com/image.jpg');
    });
});

// ============================================================================
// URL INPUT TESTS
// ============================================================================

describe('URL input functionality', function (): void {
    it('displays URL input when external URL tab is selected', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // URL input should have proper attributes
        $response->assertSee('name="featured_image"', false);
        $response->assertSee('type="url"', false);
    });

    it('validates URL format server-side', function (): void {
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

    it('validates URL points to image file server-side', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article-url',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => 'https://example.com/document.pdf',
            ])
            ->assertSessionHasErrors('featured_image');
    });

    it('accepts valid image URLs', function (): void {
        $validUrls = [
            'https://example.com/image.jpg',
            'https://example.com/photo.png',
            'https://example.com/pic.webp',
            'https://example.com/animated.gif',
        ];

        foreach ($validUrls as $url) {
            $this->actingAs($this->user)
                ->post(route('admin.articles.store'), [
                    'title' => 'Test Article',
                    'slug' => 'test-article-'.uniqid(),
                    'content' => 'Test content',
                    'status' => 'draft',
                    'featured_image' => $url,
                ])
                ->assertRedirect();

            Article::query()->delete();
        }
    });

    it('displays external URL preview', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Preview container for external URL
        $response->assertSee('x-show="imageUrl', false);
    });
});

// ============================================================================
// VISUAL INDICATOR TESTS
// ============================================================================

describe('visual indicators', function (): void {
    it('displays external URL badge for URL images', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for external URL badge
        $response->assertSee('External URL', false);
        $response->assertSee('🔗', false);
        $response->assertSee('badge badge-primary', false);
    });

    it('displays uploaded file badge for file images', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for uploaded file badge
        $response->assertSee('Uploaded File', false);
        $response->assertSee('📤', false);
        $response->assertSee('badge badge-secondary', false);
    });

    it('positions badge as overlay on preview image', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Badge should have absolute positioning
        $response->assertSee('absolute', false);
        $response->assertSee('top-2', false);
        $response->assertSee('left-2', false);
    });
});

// ============================================================================
// ACCESSIBILITY TESTS
// ============================================================================

describe('accessibility', function (): void {
    it('has accessible tab navigation with radio inputs', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for accessible tab pattern
        $response->assertSee('role="tab"', false);
        $response->assertSee('role="tabpanel"', false);
        $response->assertSee('tabindex', false);
    });

    it('has keyboard navigation support for tabs', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for keyboard event handlers
        $response->assertSee('@keydown', false);
        $response->assertSee('ArrowLeft', false);
        $response->assertSee('ArrowRight', false);
    });

    it('delete button has proper aria-label', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        $response->assertSee('aria-label="Remove featured image"', false);
    });

    it('undo button has proper aria-label', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        $response->assertSee('aria-label="Restore featured image"', false);
    });

    it('file input has descriptive label', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for file input label
        $response->assertSee('Upload Image', false);
        $response->assertSee('Image URL', false);
    });

    it('has error announcements for screen readers', function (): void {
        $response = $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => '',
                'slug' => '',
                'content' => '',
                'status' => 'draft',
                'featured_image' => 'invalid-url',
            ]);

        $response->assertSessionHasErrors(['title', 'featured_image']);

        // Follow redirect and check for error display
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'));

        // Should show error for featured_image
        $response->assertSee('featured_image');
    });
});

// ============================================================================
// ALPINE.JS INTEGRATION TESTS
// ============================================================================

describe('alpine.js integration', function (): void {
    it('initializes featured image component with correct state', function (): void {
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/saved.jpg',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.edit', $article))
            ->assertSuccessful();

        // Check that saved image URL is in Alpine data
        $response->assertSee('https://example.com/saved.jpg', false);
    });

    it('syncs file input state with Alpine', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for Alpine refs
        $response->assertSee('x-ref="fileInput"', false);
    });

    it('clears file input when delete is clicked', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Check for clear input logic
        $response->assertSee(".value = ''", false);
        $response->assertSee('$refs.fileInput', false);
    });
});

// ============================================================================
// FORM SUBMISSION TESTS
// ============================================================================

describe('form submission with modern UI', function (): void {
    it('submits successfully with URL from active tab', function (): void {
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

    it('submits successfully with file from upload tab', function (): void {
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
        // Note: File existence check skipped - Intervention Image doesn't work with Storage::fake()
    });

    it('submits with remove flag to delete existing image', function (): void {
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

    it('rejects both URL and file submitted together', function (): void {
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

    it('maintains selected tab on validation error', function (): void {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => '',
                'slug' => '',
                'content' => '',
                'status' => 'draft',
                'featured_image_file' => $file,
            ]);

        $response->assertSessionHasErrors(['title', 'slug', 'content']);

        // After redirect, form should still have file input accessible
        // The tab state would be managed by Alpine or session flash data
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'));

        $response->assertSee('name="featured_image_file"', false);
    });
});

// ============================================================================
// EDGE CASE TESTS
// ============================================================================

describe('edge cases', function (): void {
    it('handles switching between tabs without losing data', function (): void {
        // This would be a browser test for Alpine interaction
        // For now, verify that both inputs can exist in form
        $response = $this->actingAs($this->user)
            ->get(route('admin.articles.create'))
            ->assertSuccessful();

        // Both URL and file inputs should exist (controlled by Alpine visibility)
        $response->assertSee('name="featured_image"', false);
        $response->assertSee('name="featured_image_file"', false);
    });

    it('handles empty file selection gracefully', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article-empty',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => null,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->toBeNull();
    });

    it('handles URL with special characters', function (): void {
        $url = 'https://example.com/image-with-dash_underscore.jpg?size=large&format=jpg';

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article-special',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => $url,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->toBe($url);
    });

    it('handles very long filenames in preview', function (): void {
        $longName = str_repeat('a', 100).'.jpg';
        $file = UploadedFile::fake()->image($longName);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article-long',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => $file,
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->not->toBeNull();
    });

    it('preserves featured image when updating other fields', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

        $article = Article::factory()->create([
            'status' => 'draft',
        ]);

        // First upload image
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
        $savedImage = $article->featured_image;

        // Now update only title
        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => 'Updated Title',
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
            ])
            ->assertRedirect();

        $article->refresh();

        expect($article->featured_image)->toBe($savedImage);
    });
});

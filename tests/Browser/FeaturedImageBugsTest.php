<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

// ============================================================================
// BUG 1: URL Validation Tests
// Modern CDN/dynamic image URLs don't have file extensions in the path
// ============================================================================
// BROWSER TEST STRATEGY: Pre-create articles with CDN URLs via factory
// (backend validation is tested in feature tests), then verify we can VIEW
// and EDIT them without errors. We do NOT test form submission in browser
// tests due to method spoofing limitations.
// ============================================================================

describe('url validation accepts modern cdn urls', function (): void {
    it('can view article with imgur-style url without file extension', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Pre-create article with Imgur URL via factory
        $article = Article::factory()->draft()->create([
            'title' => 'Article with Imgur Image',
            'slug' => 'article-imgur-image',
            'featured_image' => 'https://i.imgur.com/abc123',
        ]);

        // Verify we can edit it without validation errors
        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->assertValue('input[name="featured_image"]', 'https://i.imgur.com/abc123')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });

    it('can view article with unsplash-style url without file extension', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Pre-create article with Unsplash URL via factory
        $article = Article::factory()->published()->create([
            'title' => 'Article with Unsplash Image',
            'slug' => 'article-unsplash-image',
            'featured_image' => 'https://images.unsplash.com/photo-123456',
        ]);

        // Verify we can edit it without validation errors
        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->assertValue('input[name="featured_image"]', 'https://images.unsplash.com/photo-123456')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });

    it('can view article with cdn url with query parameters', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Pre-create article with CDN URL containing query params
        $article = Article::factory()->published()->create([
            'title' => 'Article with CDN Query Params',
            'slug' => 'article-cdn-query',
            'featured_image' => 'https://cdn.example.com/image?w=800&h=600&fit=crop',
        ]);

        // Verify we can edit it without validation errors
        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->assertValue('input[name="featured_image"]', 'https://cdn.example.com/image?w=800&h=600&fit=crop')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });

    it('can view article with cloudflare images url', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Pre-create article with Cloudflare Images URL
        $article = Article::factory()->draft()->create([
            'title' => 'Article with Cloudflare Image',
            'slug' => 'article-cloudflare-image',
            'featured_image' => 'https://imagedelivery.net/abc123/def456/public',
        ]);

        // Verify we can edit it without validation errors
        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->assertValue('input[name="featured_image"]', 'https://imagedelivery.net/abc123/def456/public')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });
});

describe('url input disabled when upload tab active', function (): void {
    it('disables url field when upload tab is clicked', function (): void {
        $article = Article::factory()->draft()->create();

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Click on the "Upload File" tab to activate it
        $page->click('input[value="upload_file"]')
            ->wait(1) // Wait for Alpine to update
            ->assertAttribute('input[name="featured_image"]', 'disabled', 'disabled');
    });
});

// ============================================================================
// Form Interaction Tests - No Submission
// Verify UI elements work without testing actual form submission
// (Form submission is tested in feature tests)
// ============================================================================

describe('form ui interactions', function (): void {
    it('can switch between external url and upload tabs', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->draft()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Verify External URL tab is active by default
        $page->assertSee('Edit Article')
            ->assertValue('input[name="featured_image"]', 'https://example.com/image.jpg')
            // Switch to Upload tab
            ->click('input[value="upload_file"]')
            ->wait(1)
            // URL input should be disabled
            ->assertAttribute('input[name="featured_image"]', 'disabled', 'disabled')
            // Switch back to External URL tab
            ->click('input[value="external_url"]')
            ->wait(1)
            // URL input should be enabled again
            ->assertAttributeMissing('input[name="featured_image"]', 'disabled')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });

    it('can change status dropdown without errors', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->draft()->create([
            'featured_image' => 'https://i.imgur.com/test123',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'published')
            ->wait(0.5)
            ->select('status', 'draft')
            ->wait(0.5)
            ->select('status', 'published')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });
});

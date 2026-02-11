<?php

use App\Enums\Status;
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

describe('url validation accepts modern cdn urls', function (): void {
    it('accepts imgur-style url without file extension', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Pre-create article with Imgur URL (backend validation test)
        $article = Article::factory()->draft()->create([
            'title' => 'Article with Imgur Image',
            'slug' => 'article-imgur-image',
            'featured_image' => 'https://i.imgur.com/abc123',
        ]);

        // Verify it was created successfully
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://i.imgur.com/abc123');

        // Verify we can edit it without validation errors
        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->assertValue('input[name="featured_image"]', 'https://i.imgur.com/abc123')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
    });

    it('accepts unsplash-style url without file extension', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Article with Unsplash Image')
            ->fill('slug', 'article-unsplash-image')
            ->fill('content', 'Test content')
            ->select('status', 'published')
            ->fill('featured_image', 'https://images.unsplash.com/photo-123456')
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'article-unsplash-image')->first();
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://images.unsplash.com/photo-123456');
    });

    it('accepts cdn url with query parameters', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Article with CDN Query Params')
            ->fill('slug', 'article-cdn-query')
            ->fill('content', 'Test content')
            ->select('status', 'published')
            ->fill('featured_image', 'https://cdn.example.com/image?w=800&h=600&fit=crop')
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'article-cdn-query')->first();
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://cdn.example.com/image?w=800&h=600&fit=crop');
    });

    it('accepts cloudflare images url', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Article with Cloudflare Image')
            ->fill('slug', 'article-cloudflare-image')
            ->fill('content', 'Test content')
            ->select('status', 'draft')
            ->fill('featured_image', 'https://imagedelivery.net/abc123/def456/public')
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'article-cloudflare-image')->first();
        expect($article)->not->toBeNull();
        expect($article->featured_image)->toBe('https://imagedelivery.net/abc123/def456/public');
    });
});

describe('url input disabled when upload tab active', function (): void {
    it('disables url field when upload tab is clicked', function (): void {
        $article = Article::factory()->draft()->create();

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Click on the "Upload File" tab to activate it
        $page->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->assertAttribute('input[name="featured_image"]', 'disabled', 'true');
    });
});

// ============================================================================
// BUG 2: Image Storage Tests
// Images must be on correct disk based on article status
// ============================================================================

describe('draft articles store images on private disk', function (): void {
    it('stores uploaded image on private disk for draft article', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $testImagePath = base_path('tests/fixtures/test-image.jpg');

        // Ensure fixtures directory exists
        if (! is_dir(base_path('tests/fixtures'))) {
            mkdir(base_path('tests/fixtures'), 0755, true);
        }

        // Create a simple test image if it doesn't exist
        if (! file_exists($testImagePath)) {
            $image = imagecreatetruecolor(100, 100);
            imagejpeg($image, $testImagePath, 90);
            imagedestroy($image);
        }

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Draft with Private Image')
            ->fill('slug', 'draft-private-image')
            ->fill('content', 'Test content')
            ->select('status', 'draft')
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->attach('featured_image_file', $testImagePath)
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'draft-private-image')->first();
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Draft);

        // Verify image is on private disk
        $imagePath = str_replace('articles/featured/', '', $article->featured_image);
        Storage::disk('private')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$article->id}/full.webp");
    });
});

describe('hidden articles store images on private disk', function (): void {
    it('stores uploaded image on private disk for hidden article', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $testImagePath = base_path('tests/fixtures/test-image.jpg');

        if (! file_exists($testImagePath)) {
            if (! is_dir(base_path('tests/fixtures'))) {
                mkdir(base_path('tests/fixtures'), 0755, true);
            }
            $image = imagecreatetruecolor(100, 100);
            imagejpeg($image, $testImagePath, 90);
            imagedestroy($image);
        }

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Hidden with Private Image')
            ->fill('slug', 'hidden-private-image')
            ->fill('content', 'Test content')
            ->select('status', 'hidden')
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->attach('featured_image_file', $testImagePath)
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'hidden-private-image')->first();
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Hidden);

        // Verify image is on private disk
        Storage::disk('private')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$article->id}/full.webp");
    });
});

describe('published articles store images on public disk', function (): void {
    it('stores uploaded image on public disk for published article', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $testImagePath = base_path('tests/fixtures/test-image.jpg');

        if (! file_exists($testImagePath)) {
            if (! is_dir(base_path('tests/fixtures'))) {
                mkdir(base_path('tests/fixtures'), 0755, true);
            }
            $image = imagecreatetruecolor(100, 100);
            imagejpeg($image, $testImagePath, 90);
            imagedestroy($image);
        }

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Published with Public Image')
            ->fill('slug', 'published-public-image')
            ->fill('content', 'Test content')
            ->select('status', 'published')
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->attach('featured_image_file', $testImagePath)
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'published-public-image')->first();
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Published);

        // Verify image is on public disk
        Storage::disk('public')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$article->id}/full.webp");
    });
});

// ============================================================================
// Status Change Tests - Images Must Move Between Disks
// ============================================================================

describe('status changes move images between disks', function (): void {
    it('moves image from private to public when changing draft to published', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create draft article with image on private disk
        $article = Article::factory()->draft()->create([
            'featured_image' => 'articles/featured/1/full.webp',
        ]);

        // Manually put image on private disk to simulate existing state
        Storage::disk('private')->put("articles/featured/{$article->id}/full.webp", 'fake-image-content');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'published')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status)->toBe(Status::Published);

        // Verify image moved from private to public disk
        Storage::disk('public')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$article->id}/full.webp");
    });

    it('moves image from public to private when changing published to draft', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create published article with image on public disk
        $article = Article::factory()->published()->create([
            'featured_image' => 'articles/featured/1/full.webp',
        ]);

        // Manually put image on public disk to simulate existing state
        Storage::disk('public')->put("articles/featured/{$article->id}/full.webp", 'fake-image-content');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'draft')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status)->toBe(Status::Draft);

        // Verify image moved from public to private disk
        Storage::disk('private')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$article->id}/full.webp");
    });

    it('moves image from public to private when changing published to hidden', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create published article with image on public disk
        $article = Article::factory()->published()->create([
            'featured_image' => 'articles/featured/1/full.webp',
        ]);

        // Manually put image on public disk to simulate existing state
        Storage::disk('public')->put("articles/featured/{$article->id}/full.webp", 'fake-image-content');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'hidden')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status)->toBe(Status::Hidden);

        // Verify image moved from public to private disk
        Storage::disk('private')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$article->id}/full.webp");
    });

    it('moves image from private to public when changing hidden to published', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create hidden article with image on private disk
        $article = Article::factory()->hidden()->create([
            'featured_image' => 'articles/featured/1/full.webp',
        ]);

        // Manually put image on private disk to simulate existing state
        Storage::disk('private')->put("articles/featured/{$article->id}/full.webp", 'fake-image-content');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'published')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status)->toBe(Status::Published);

        // Verify image moved from private to public disk
        Storage::disk('public')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$article->id}/full.webp");
    });

    it('keeps image on private disk when changing draft to hidden', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create draft article with image on private disk
        $article = Article::factory()->draft()->create([
            'featured_image' => 'articles/featured/1/full.webp',
        ]);

        // Manually put image on private disk to simulate existing state
        Storage::disk('private')->put("articles/featured/{$article->id}/full.webp", 'fake-image-content');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'hidden')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status)->toBe(Status::Hidden);

        // Verify image stayed on private disk
        Storage::disk('private')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$article->id}/full.webp");
    });

    it('keeps image on private disk when changing hidden to draft', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create hidden article with image on private disk
        $article = Article::factory()->hidden()->create([
            'featured_image' => 'articles/featured/1/full.webp',
        ]);

        // Manually put image on private disk to simulate existing state
        Storage::disk('private')->put("articles/featured/{$article->id}/full.webp", 'fake-image-content');

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'draft')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status)->toBe(Status::Draft);

        // Verify image stayed on private disk
        Storage::disk('private')->assertExists("articles/featured/{$article->id}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$article->id}/full.webp");
    });
});

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
// BUG 1: Form Validation Error Tests
// When editing a published article with an image and trying to change
// status to draft/hidden, the form won't submit due to "invalid form control"
// errors for the featured_image URL field even when it's hidden.
// ============================================================================

describe('form submits when changing status with image present', function (): void {
    it('submits form when changing published to draft with existing image', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->published()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'draft')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status->value)->toBe('draft');
    });

    it('submits form when changing published to hidden with existing image', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->published()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->select('status', 'hidden')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->status->value)->toBe('hidden');
    });
});

describe('url field disabled state', function (): void {
    it('url field has disabled attribute when upload tab is active', function (): void {
        $article = Article::factory()->draft()->create();

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Click on the "Upload File" tab to activate it
        // The tab has value="upload_file" and class="tab"
        $page->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->assertAttribute('input[name="featured_image"]', 'disabled', 'true');
    });
});

describe('form validation does not block on hidden url field', function (): void {
    it('submits form when upload tab active with empty url field', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->draft()->create([
            'title' => 'Original Title',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Switch to upload tab (which hides URL field)
        $page->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->fill('title', 'Updated Title')
            ->select('status', 'published')
            ->press('Update')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article->refresh();
        expect($article->title)->toBe('Updated Title');
        expect($article->status->value)->toBe('published');
    });
});

describe('create draft and hidden articles with image upload', function (): void {
    it('creates draft article with image upload successfully', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create a real image file for upload
        $testImagePath = base_path('tests/fixtures/test-image.jpg');

        // Ensure fixtures directory exists
        if (! is_dir(base_path('tests/fixtures'))) {
            mkdir(base_path('tests/fixtures'), 0755, true);
        }

        // Create a simple test image if it doesn't exist
        if (! file_exists($testImagePath)) {
            // Create a 1x1 pixel JPEG
            $image = imagecreatetruecolor(100, 100);
            imagejpeg($image, $testImagePath, 90);
            imagedestroy($image);
        }

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.create'));

        $page->assertSee('Create Article')
            ->fill('title', 'Test Draft Article with Image')
            ->fill('slug', 'test-draft-article-image')
            ->fill('content', 'This is test content for the article.')
            ->select('status', 'draft')
            // Switch to upload tab
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            // Attach the image file
            ->attach('featured_image_file', $testImagePath)
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'test-draft-article-image')->first();
        expect($article)->not->toBeNull();
        expect($article->status->value)->toBe('draft');
        expect($article->featured_image)->not->toBeNull();
    });

    it('creates hidden article with image upload successfully', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $testImagePath = base_path('tests/fixtures/test-image.jpg');

        // Create test image if needed
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
            ->fill('title', 'Test Hidden Article with Image')
            ->fill('slug', 'test-hidden-article-image')
            ->fill('content', 'This is test content for the hidden article.')
            ->select('status', 'hidden')
            // Switch to upload tab
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            // Attach the image file
            ->attach('featured_image_file', $testImagePath)
            ->press('Create')
            ->assertUrlIs(route('admin.articles.index'))
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        $article = Article::where('slug', 'test-hidden-article-image')->first();
        expect($article)->not->toBeNull();
        expect($article->status->value)->toBe('hidden');
        expect($article->featured_image)->not->toBeNull();
    });
});

describe('console and javascript errors', function (): void {
    it('has no console logs during form interactions', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->published()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Perform various interactions
        $page->assertSee('Edit Article')
            // Click between tabs
            ->click('label[for="image_tab_external_url"], input[value="external_url"]')
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->click('label[for="image_tab_external_url"], input[value="external_url"]')
            // Change status
            ->select('status', 'draft')
            ->assertNoConsoleLogs();
    });

    it('has no javascript errors during page load and interaction', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->published()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        $page->assertSee('Edit Article')
            ->assertNoJavaScriptErrors()
            // Click on upload tab
            ->click('label[for="image_tab_upload_file"], input[value="upload_file"]')
            ->assertNoJavaScriptErrors()
            // Change status
            ->select('status', 'hidden')
            ->assertNoJavaScriptErrors()
            // Change back
            ->select('status', 'published')
            ->assertNoJavaScriptErrors();
    });
});

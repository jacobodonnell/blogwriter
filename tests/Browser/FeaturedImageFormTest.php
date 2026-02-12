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
// Form UI Tests - Visual/DOM Verification Only
// Form submission logic is tested in feature tests
// ============================================================================

describe('url field disabled state', function (): void {
    it('url field has disabled attribute when upload tab is active', function (): void {
        $article = Article::factory()->draft()->create();

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Click on the "Upload File" tab to activate it
        $page->click('input[value="upload_file"]')
            ->wait(1) // Wait for Alpine to update
            ->assertAttribute('input[name="featured_image"]', 'disabled', 'disabled');
    });
});

describe('form validation does not block on hidden url field', function (): void {
    it('url field is disabled when upload tab is active', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $article = Article::factory()->draft()->create([
            'title' => 'Original Title',
        ]);

        $page = $this->actingAs($this->user)
            ->visit(route('admin.articles.edit', $article));

        // Switch to upload tab (which disables URL field)
        $page->click('input[value="upload_file"]')
            ->wait(1)
            ->assertAttribute('input[name="featured_image"]', 'disabled', 'disabled')
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();
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
            ->click('input[value="external_url"]')
            ->wait(0.5)
            ->click('input[value="upload_file"]')
            ->wait(0.5)
            ->click('input[value="external_url"]')
            ->wait(0.5)
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
            ->click('input[value="upload_file"]')
            ->wait(0.5)
            ->assertNoJavaScriptErrors()
            // Change status
            ->select('status', 'draft')
            ->assertNoJavaScriptErrors()
            // Change back
            ->select('status', 'published')
            ->assertNoJavaScriptErrors();
    });
});

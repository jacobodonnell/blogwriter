<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginAndVisitRevertEditor(Article $article): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/articles/'.$article->id.'/edit');

    return $page;
}

it('reverts content without JS errors', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Original Content',
        'published_at' => null,
    ]);

    $page = loginAndVisitRevertEditor($article);

    // Wait for Tiptap to initialize
    $page->wait(3);

    // Type additional text into the editor
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' extra text')
        ->wait(0.5);

    // Click the revert button for content
    $page->click('[data-test="revert-content"]')
        ->wait(0.5);

    // Key assertion: no JS errors from editor.setContent()
    $page->assertNoJavaScriptErrors();
})->group('slow');

it('reverts title and hides revert button', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'My Original Title',
        'published_at' => null,
    ]);

    $page = loginAndVisitRevertEditor($article);

    $page->wait(3);

    // Change the title
    $page->clear('input[name="title"]')
        ->type('input[name="title"]', 'Changed Title')
        ->wait(0.5);

    // Revert button should be visible
    $page->assertVisible('[data-test="revert-title"]');

    // Click revert
    $page->click('[data-test="revert-title"]')
        ->wait(0.5);

    // Title should be restored
    $page->assertValue('input[name="title"]', 'My Original Title');

    // Revert button should be hidden (x-show sets display:none)
    $page->assertMissing('[data-test="revert-title"]');

    // Save button should show inactive state
    $page->assertAttributeContains('@save-button', 'class', 'btn-ghost');
})->group('slow');

it('reverts featured image from photo back to external URL', function (): void {
    $photo = Photo::factory()->for($this->user)->create();

    $article = Article::factory()->published()->for($this->user)->create([
        'external_featured_img_url' => 'https://example.com/original.jpg',
        'photo_id' => null,
    ]);

    // Create a draft that changes to using a photo
    $article->update([
        'draft' => [
            'photo_id' => $photo->id,
            'external_featured_img_url' => null,
        ],
    ]);

    $page = loginAndVisitRevertEditor($article);

    $page->wait(3);

    // Photo should be selected in dropdown (draft state)
    $page->assertSeeIn('[data-test="photo-select-trigger"]', $photo->alt_text ?: 'Photo #'.$photo->id);

    // Click revert button for featured image
    $page->click('[data-test="revert-featuredImage"]')
        ->wait(0.5);

    // Dropdown trigger should show no photo selected (external URL mode)
    $page->assertSeeIn('[data-test="photo-select-trigger"]', 'Using external URL');

    // URL field should be visible with the original URL
    $page->assertVisible('@featured-image-url');
    $page->assertValue('@featured-image-url', 'https://example.com/original.jpg');

    // No JS errors
    $page->assertNoJavaScriptErrors();
})->group('slow');

it('reverts all dirty fields and clears draft state', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Original Title',
        'summary' => 'Original Summary',
        'published_at' => null,
    ]);

    $page = loginAndVisitRevertEditor($article);

    $page->wait(3);

    // Change title
    $page->clear('input[name="title"]')
        ->type('input[name="title"]', 'Changed Title')
        ->wait(0.3);

    // Change summary
    $page->clear('@summary-field')
        ->type('@summary-field', 'Changed Summary')
        ->wait(0.3);

    // Revert title
    $page->click('[data-test="revert-title"]')
        ->wait(0.3);

    // Revert summary
    $page->click('[data-test="revert-summary"]')
        ->wait(0.5);

    // Save button should show inactive state
    $page->assertSeeIn('@save-button-label', 'Saved');
    $page->assertAttributeContains('@save-button', 'class', 'btn-ghost');
})->group('slow');

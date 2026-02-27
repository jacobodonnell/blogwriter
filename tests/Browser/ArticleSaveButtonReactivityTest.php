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

function loginAndVisitEditor(Article $article): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/articles/'.$article->id.'/edit');

    return $page;
}

function uploadPhotoViaModal(mixed $page): void
{
    $page->click('@upload-new-photo')
        ->wait(0.5)
        ->attach('@photo-file-picker', realpath(__DIR__.'/../fixtures/test-image.jpg'))
        ->fill('@photo-alt-text', 'Test image alt text')
        ->click('@attach-photo')
        ->wait(0.5);
}

it('shows Upload Photo & Save Draft after uploading a photo on draft article', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $page = loginAndVisitEditor($article);

    $page->assertSeeIn('@save-button-label', 'Saved');

    uploadPhotoViaModal($page);

    $page->assertSeeIn('@save-button-label', 'Upload Photo & Save Draft')
        ->assertAttributeContains('@save-button', 'class', 'btn-success');
})->group('slow');

it('clears upload prefix and shows Save Draft when selecting existing photo after upload', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $photo = Photo::factory()->for($this->user)->create();

    $page = loginAndVisitEditor($article);

    uploadPhotoViaModal($page);

    $page->assertSeeIn('@save-button-label', 'Upload Photo & Save Draft');

    $page->click('[data-test="photo-select-trigger"]')
        ->wait(0.3)
        ->click('[data-test="photo-option-'.$photo->id.'"]')
        ->wait(1);

    $page->assertSeeIn('@save-button-label', 'Save Draft')
        ->assertAttributeContains('@save-button', 'class', 'btn-primary');
})->group('slow');

it('shows Upload Photo & Save Changes for published article with photo', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    $page = loginAndVisitEditor($article);
    $page->assertSeeIn('@save-button-label', 'Published');

    uploadPhotoViaModal($page);

    $page->assertSeeIn('@save-button-label', 'Upload Photo & Publish')
        ->assertAttributeContains('@save-button', 'class', 'btn-success');
})->group('slow');

it('shows Upload Photo & Publish when draft status changed to published with photo', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $page = loginAndVisitEditor($article);

    // Use script to change Alpine state directly, avoiding the form auto-submit
    // that triggers a broken AJAX preview request
    $page->script("document.querySelector('[data-test=\"status-select\"]')._x_model.set('public')");
    $page->wait(0.5);

    $page->assertSeeIn('@save-button-label', 'Publish Article');

    uploadPhotoViaModal($page);

    $page->assertSeeIn('@save-button-label', 'Upload Photo & Publish')
        ->assertAttributeContains('@save-button', 'class', 'btn-success');
})->group('slow');

it('has btn-success class when photo uploaded and btn-primary when not', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $page = loginAndVisitEditor($article);

    $page->assertAttributeContains('@save-button', 'class', 'btn-ghost');

    uploadPhotoViaModal($page);

    $page->assertAttributeContains('@save-button', 'class', 'btn-success');
})->group('slow');

it('save button deactivates when editor content is reverted to original', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => 'Original content here.',
    ]);

    $page = loginAndVisitEditor($article);
    $page->wait(3); // wait for Tiptap to init

    // Button starts inactive
    $page->assertSeeIn('@save-button-label', 'Saved')
        ->assertAttributeContains('@save-button', 'class', 'btn-ghost');

    // Type something into the editor — button activates
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' extra')
        ->wait(0.5);

    $page->assertAttributeContains('@save-button', 'class', 'btn-primary');

    // Revert: select all and retype the exact original content
    $page->keys('[data-test="content-editor"] .tiptap', 'Control+a')
        ->type('[data-test="content-editor"] .tiptap', 'Original content here.')
        ->wait(0.5);

    // Button should deactivate again
    $page->assertSeeIn('@save-button-label', 'Saved')
        ->assertAttributeContains('@save-button', 'class', 'btn-ghost');
})->group('slow');

it('save button activates immediately when category is changed', function (): void {
    $category = App\Models\Category::factory()->create();
    $article = Article::factory()->draft()->for($this->user)->create();

    $page = loginAndVisitEditor($article);
    $page->wait(2);

    $page->assertSeeIn('@save-button-label', 'Saved')
        ->assertAttributeContains('@save-button', 'class', 'btn-ghost');

    $page->select('select[name="category_id"]', (string) $category->id)
        ->wait(0.3);

    $page->assertAttributeContains('@save-button', 'class', 'btn-primary');
})->group('slow');

it('save button deactivates when category is reverted to original', function (): void {
    $category = App\Models\Category::factory()->create();
    $article = Article::factory()->draft()->for($this->user)->create([
        'category_id' => null,
    ]);

    $page = loginAndVisitEditor($article);
    $page->wait(2);

    $page->assertSeeIn('@save-button-label', 'Saved')
        ->assertAttributeContains('@save-button', 'class', 'btn-ghost');

    // Change category — button activates
    $page->select('select[name="category_id"]', (string) $category->id)
        ->wait(0.3);
    $page->assertAttributeContains('@save-button', 'class', 'btn-primary');

    // Revert category — button deactivates
    $page->select('select[name="category_id"]', '')
        ->wait(1);

    $page->assertSeeIn('@save-button-label', 'Saved')
        ->assertAttributeContains('@save-button', 'class', 'btn-ghost');
})->group('slow');

it('save button activates when SEO meta field is changed', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    $page = loginAndVisitEditor($article);
    $page->wait(2);

    $page->assertAttributeContains('@save-button', 'class', 'btn-ghost');

    // Open SEO section and type in meta title
    $page->click('@seo-toggle')
        ->wait(0.5)
        ->type('input[name="meta[meta_title]"]', 'Custom SEO Title')
        ->wait(0.5);

    $page->assertAttributeContains('@save-button', 'class', 'btn-primary');
})->group('slow');

it('save button deactivates when draft category is reverted to live value', function (): void {
    $liveCategory = App\Models\Category::factory()->create();
    $draftCategory = App\Models\Category::factory()->create();

    $article = Article::factory()->published()->for($this->user)->create([
        'title' => 'Same Title',
        'content' => 'Same content for both.',
        'category_id' => $liveCategory->id,
    ]);

    // Manually set the draft so only category_id differs
    $article->update([
        'draft' => [
            'title' => 'Same Title',
            'content' => 'Same content for both.',
            'category_id' => $draftCategory->id,
        ],
    ]);

    $page = loginAndVisitEditor($article);
    $page->wait(2);

    // Editor opens with draft category — button should be dirty
    $page->assertSeeIn('@save-button-label', 'Publish Updates');

    // Revert category to live value — button should deactivate
    $page->select('select[name="category_id"]', (string) $liveCategory->id)
        ->wait(1);
    $page->assertSeeIn('@save-button-label', 'Published')
        ->assertAttributeContains('@save-button', 'class', 'btn-ghost');
})->group('slow');

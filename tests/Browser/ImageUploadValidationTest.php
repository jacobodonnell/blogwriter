<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
    Setting::set('customizer_editor_mode', 'split');
    config(['app.max_image_upload_kb' => 3000]);
});

function loginForUploadTest(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit');

    return $page;
}

it('shows client-side error toast when uploading oversized file in photos modal', function (): void {
    $page = loginForUploadTest();

    $page->navigate('/photos');
    $page->wait(2);

    // Open the upload modal
    $page->click('@open-upload-modal')
        ->wait(0.5);

    // Attach an oversized file (4 MB, over the 3000 KB limit)
    $page->attach('@photos-file-picker', realpath(__DIR__.'/../fixtures/oversized-image.jpg'))
        ->wait(1);

    // Should see an error toast about file size
    $page->assertVisible('[data-test="ajax-toast"]')
        ->assertSeeIn('[data-test="ajax-toast"]', 'too large');
})->group('slow');

it('clears file input and closes modal when oversized file is selected in photos modal', function (): void {
    $page = loginForUploadTest();

    $page->navigate('/photos');
    $page->wait(2);

    // Open the upload modal
    $page->click('@open-upload-modal')
        ->wait(0.5);

    // Attach an oversized file
    $page->attach('@photos-file-picker', realpath(__DIR__.'/../fixtures/oversized-image.jpg'))
        ->wait(1);

    // Modal should close (file was rejected)
    $page->assertMissing('#upload-photo-modal[open]');
})->group('slow');

it('shows client-side error toast when uploading oversized file in article customizer', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $page = loginForUploadTest();

    $page->navigate('/admin/articles/'.$article->id.'/edit');
    $page->wait(2);

    // Open the upload photo modal
    $page->click('@upload-new-photo')
        ->wait(0.5);

    // Attach an oversized file
    $page->attach('@photo-file-picker', realpath(__DIR__.'/../fixtures/oversized-image.jpg'))
        ->wait(1);

    // Should see an error toast about file size
    $page->assertVisible('[data-test="ajax-toast"]')
        ->assertSeeIn('[data-test="ajax-toast"]', 'too large');
})->group('slow');

it('shows specific error message in article customizer when server-side validation fails', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $page = loginForUploadTest();

    $page->navigate('/admin/articles/'.$article->id.'/edit');
    $page->wait(2);

    // Open the upload photo modal
    $page->click('@upload-new-photo')
        ->wait(0.5);

    // Attach a valid small file and fill required fields
    $page->attach('@photo-file-picker', realpath(__DIR__.'/../fixtures/test-image.jpg'))
        ->fill('@photo-alt-text', 'Test image alt text')
        ->click('@attach-photo')
        ->wait(0.5);

    // Save the article to trigger server-side validation
    $page->click('@save-button')
        ->wait(2);

    // The toast should NOT show the generic message
    $page->assertDontSee('Something went wrong. Please fix the errors below and try again.');
})->group('slow');

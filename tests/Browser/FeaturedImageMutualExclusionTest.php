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

function loginAndVisitArticleEditor(Article $article): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/articles/'.$article->id.'/edit');

    return $page;
}

it('clears external URL when selecting a photo', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $photo = Photo::factory()->for($this->user)->create();

    $page = loginAndVisitArticleEditor($article);

    // Open URL field and type a URL
    $page->click('@url-toggle')
        ->wait(0.3)
        ->type('@featured-image-url', 'https://example.com/image.jpg')
        ->wait(0.3);

    // Select a photo from dropdown
    $page->click('[data-test="photo-select-trigger"]')
        ->wait(0.3)
        ->click('[data-test="photo-option-'.$photo->id.'"]')
        ->wait(0.5);

    // URL input should be cleared
    $page->assertValue('@featured-image-url', '');
})->group('slow');

it('clears photo selection when entering an external URL', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $photo = Photo::factory()->for($this->user)->create();

    $page = loginAndVisitArticleEditor($article);

    // Select a photo first
    $page->click('[data-test="photo-select-trigger"]')
        ->wait(0.3)
        ->click('[data-test="photo-option-'.$photo->id.'"]')
        ->wait(0.3);

    // Open URL field and type a URL
    $page->click('@url-toggle')
        ->wait(0.3)
        ->type('@featured-image-url', 'https://example.com/image.jpg')
        ->wait(0.5);

    // Dropdown trigger should show no photo selected (external URL takes over)
    $page->assertSeeIn('[data-test="photo-select-trigger"]', 'Using external URL');
})->group('slow');

it('clears upload state when selecting a photo', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $photo = Photo::factory()->for($this->user)->create();

    $page = loginAndVisitArticleEditor($article);

    // Upload a photo via modal
    $page->click('@upload-new-photo')
        ->wait(0.5)
        ->attach('@photo-file-picker', realpath(__DIR__.'/../fixtures/test-image.jpg'))
        ->fill('@photo-alt-text', 'Test image alt text')
        ->click('@attach-photo')
        ->wait(0.5);

    $page->assertSeeIn('@save-button-label', 'Upload Photo & Save Draft');

    // Select an existing photo
    $page->click('[data-test="photo-select-trigger"]')
        ->wait(0.3)
        ->click('[data-test="photo-option-'.$photo->id.'"]')
        ->wait(0.5);

    // Save button should show dirty state (selecting a photo is a real change)
    $page->assertSeeIn('@save-button-label', 'Save Draft');
})->group('slow');

it('clears upload state when entering an external URL', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    $page = loginAndVisitArticleEditor($article);

    // Upload a photo via modal
    $page->click('@upload-new-photo')
        ->wait(0.5)
        ->attach('@photo-file-picker', realpath(__DIR__.'/../fixtures/test-image.jpg'))
        ->fill('@photo-alt-text', 'Test image alt text')
        ->click('@attach-photo')
        ->wait(0.5);

    $page->assertSeeIn('@save-button-label', 'Upload Photo & Save Draft');

    // Open URL field and type a URL
    $page->click('@url-toggle')
        ->wait(0.3)
        ->type('@featured-image-url', 'https://example.com/image.jpg')
        ->wait(0.5);

    // Save button should show dirty state (entering a URL is a real change)
    $page->assertSeeIn('@save-button-label', 'Save Draft');
})->group('slow');

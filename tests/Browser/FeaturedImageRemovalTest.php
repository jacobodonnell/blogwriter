<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
    Setting::set('customizer_editor_mode', 'split');
});

function loginAndVisitEditorForRemoval(Article $article): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/articles/'.$article->id.'/edit');

    // Wait for Tiptap to initialize so submitFullSave() can call editor.getMarkdown()
    $page->wait(3);

    return $page;
}

it('clears an uploaded photo when user selects no featured image', function (): void {
    $photo = Photo::factory()->for($this->user)->create();
    $article = Article::factory()->draft()->for($this->user)->create([
        'photo_id' => $photo->id,
    ]);

    $page = loginAndVisitEditorForRemoval($article);

    // Select "No featured image" option
    $page->click('[data-test="photo-select-trigger"]')
        ->wait(0.3)
        ->click('[data-test="photo-option-none"]')
        ->wait(0.3);

    // Save the article and wait for redirect
    $page->click('@save-button')
        ->wait(2.0);

    expect($article->fresh()->photo_id)->toBeNull();
})->group('slow');

it('clears an external URL when user removes the image URL', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'external_featured_img_url' => 'https://example.com/image.jpg',
    ]);

    $page = loginAndVisitEditorForRemoval($article);

    // Click "Remove image URL" button
    $page->click('[data-test="remove-external-url"]')
        ->wait(0.5);

    // Save the article and wait for redirect
    $page->click('@save-button')
        ->wait(2.0);

    expect($article->fresh()->external_featured_img_url)->toBeNull();
})->group('slow');

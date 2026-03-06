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
});

function loginAndVisitUndoRedoEditor(Article $article): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/articles/'.$article->id.'/edit');

    return $page;
}

it('shows undo and redo buttons disabled on a fresh article', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Fresh Content',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    $page->assertVisible('[data-test="toolbar-undo"]')
        ->assertVisible('[data-test="toolbar-redo"]')
        ->assertDisabled('[data-test="toolbar-undo"]')
        ->assertDisabled('[data-test="toolbar-redo"]');

})->group('slow');

it('enables undo after typing in the editor', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Start Content',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    // Type into the Tiptap editor
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' added text');

    $page->wait(1);

    $page->assertEnabled('[data-test="toolbar-undo"]');

})->group('slow');

it('reverts typed content on undo click', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Undo Test',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    // Capture the initial content
    $page->assertScript(
        "document.querySelector('[data-test=\"content-editor\"] .tiptap').innerText.includes('Undo Test')",
        true,
    );

    // Type new text
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' extra words');

    $page->wait(1);

    // Click undo
    $page->click('[data-test="toolbar-undo"]');

    $page->wait(1);

    // Content should contain original text
    $page->assertScript(
        "document.querySelector('[data-test=\"content-editor\"] .tiptap').innerText.includes('Undo Test')",
        true,
    );

})->group('slow');

it('reverts typed content on Cmd+Z keyboard shortcut', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Keyboard Undo',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    // Confirm initial content
    $page->assertScript(
        "document.querySelector('[data-test=\"content-editor\"] .tiptap').innerText.includes('Keyboard Undo')",
        true,
    );

    // Type new text
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' extra text');

    $page->wait(1);

    // Press Cmd+Z (Meta+Z) to undo
    $page->keys('[data-test="content-editor"] .tiptap', 'Meta+z');

    $page->wait(1);

    // Original content should still be present
    $page->assertScript(
        "document.querySelector('[data-test=\"content-editor\"] .tiptap').innerText.includes('Keyboard Undo')",
        true,
    );

})->group('slow');

it('enables redo after an undo', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Redo Test',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    // Type new text
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' new stuff');

    $page->wait(1);

    // Undo
    $page->click('[data-test="toolbar-undo"]');

    $page->wait(1);

    // Redo should now be enabled
    $page->assertEnabled('[data-test="toolbar-redo"]');

})->group('slow');

it('hides undo and redo buttons in markdown mode', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Markdown Mode Test',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    // Buttons should be visible in WYSIWYG mode
    $page->assertVisible('[data-test="toolbar-undo"]');

    // Switch to markdown mode
    $page->click('[data-test="toolbar-markdown-source"]');

    $page->wait(1);

    // Buttons should be hidden in markdown mode
    $page->assertMissing('[data-test="toolbar-undo"]')
        ->assertMissing('[data-test="toolbar-redo"]');

})->group('slow');

it('does not produce JavaScript errors during undo/redo interactions', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## JS Error Test',
        'published_at' => null,
    ]);

    $page = loginAndVisitUndoRedoEditor($article);

    $page->wait(3);

    // Type, undo, redo — full cycle
    $page->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', ' testing errors');

    $page->wait(1);

    $page->click('[data-test="toolbar-undo"]');

    $page->wait(0.5);

    $page->click('[data-test="toolbar-redo"]');

    $page->wait(0.5);

})->group('slow');

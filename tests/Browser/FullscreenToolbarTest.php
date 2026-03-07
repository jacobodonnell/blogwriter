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
    $this->article = Article::factory()->draft()->for($this->user)->create();
    Setting::set('customizer_editor_mode', 'split');
});

function loginAndEnterFullscreen(Article $article): mixed
{
    $page = visit('/login');
    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit');
    $page->navigate(route('admin.articles.edit', $article));

    $page->wait(3)
        ->click('[data-test="navbar-fullscreen"]')
        ->wait(1)
        ->click('[data-test="content-editor"] .tiptap');

    return $page;
}

it('toggles italic active state', function (): void {
    $page = loginAndEnterFullscreen($this->article);

    $page->click('[data-test="fs-toolbar-italic"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="fs-toolbar-italic"]', 'class', 'btn-active');
})->group('slow');

it('toggles h3 active state', function (): void {
    $page = loginAndEnterFullscreen($this->article);

    $page->click('[data-test="fs-toolbar-h3"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="fs-toolbar-h3"]', 'class', 'btn-active');
})->group('slow');

it('toggles bullet list active state', function (): void {
    $this->article->update(['content' => 'plain text']);
    $page = loginAndEnterFullscreen($this->article);

    $page->click('[data-test="fs-toolbar-bullet-list"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="fs-toolbar-bullet-list"]', 'class', 'btn-active');
})->group('slow');

it('toggles ordered list active state', function (): void {
    $this->article->update(['content' => 'plain text']);
    $page = loginAndEnterFullscreen($this->article);

    $page->click('[data-test="fs-toolbar-ordered-list"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="fs-toolbar-ordered-list"]', 'class', 'btn-active');
})->group('slow');

it('toggles code active state', function (): void {
    $page = loginAndEnterFullscreen($this->article);

    // Type some text first so code can be applied
    $page->type('[data-test="content-editor"] .tiptap', 'some code')
        ->wait(0.3);

    // Select all text then apply code
    $page->script("document.querySelector('[data-test=\"content-editor\"] .tiptap').focus(); document.execCommand('selectAll')");
    $page->wait(0.3);

    $page->click('[data-test="fs-toolbar-code"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="fs-toolbar-code"]', 'class', 'btn-active');
})->group('slow');

it('inserts horizontal rule', function (): void {
    $page = loginAndEnterFullscreen($this->article);

    $page->click('[data-test="fs-toolbar-hr"]')
        ->wait(0.5);

    $value = $page->value('input[name="content"]');

    expect($value)->toContain('---');
})->group('slow');

it('opens link dialog and inserts link', function (): void {
    $page = loginAndEnterFullscreen($this->article);

    // Type text and select it
    $page->type('[data-test="content-editor"] .tiptap', 'click here')
        ->wait(0.3);
    $page->script("document.querySelector('[data-test=\"content-editor\"] .tiptap').focus(); document.execCommand('selectAll')");
    $page->wait(0.3);

    $page->click('[data-test="fs-toolbar-link"]')
        ->wait(0.5)
        ->assertVisible('[data-test="link-url-input"]')
        ->fill('[data-test="link-url-input"]', 'https://example.com')
        ->click('[data-test="link-insert-btn"]')
        ->wait(0.5);

    $value = $page->value('input[name="content"]');

    expect($value)->toContain('[click here](https://example.com)');
})->group('slow');

it('toggles markdown mode in fullscreen', function (): void {
    $page = visit('/login');
    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit');
    $page->navigate(route('admin.articles.edit', $this->article));

    $page->wait(3)
        ->click('[data-test="navbar-fullscreen"]')
        ->wait(1);

    // In fullscreen WYSIWYG, the fs markdown toolbar collapse should not be visible
    $page->assertMissing('[data-test="fs-md-toolbar-split"]');

    // Toggle markdown mode via Alpine
    $page->script("(() => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        Alpine.\$data(el).toggleMarkdownMode();
    })()");

    $page->wait(0.5)
        ->assertVisible('[data-test="fs-md-toolbar-split"]');
})->group('slow');

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

// Per-file login helper is intentional: each browser test combines login + navigation
// + page-specific setup in one call. Extracting just login would save little and add indirection.
function loginToAdmin(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    return $page;
}

it('loads editor on create page', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->assertVisible('[data-test="content-editor"]');
})->group('slow');

it('loads editor on edit page with existing content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => "## Hello World\n\nThis is a **test** article with markdown content.",
    ]);

    $page = loginToAdmin();

    $page->navigate('/admin/articles/'.$article->id.'/edit')
        ->wait(3)
        ->assertVisible('[data-test="content-editor"]');
})->group('slow');

it('bold toolbar button toggles active state', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="content-editor"] .tiptap')
        ->click('[data-test="toolbar-bold"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="toolbar-bold"]', 'class', 'btn-active');
})->group('slow');

it('h2 toolbar button toggles active state', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="content-editor"] .tiptap')
        ->click('[data-test="toolbar-h2"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="toolbar-h2"]', 'class', 'btn-active');
})->group('slow');

it('blockquote toolbar button toggles active state', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="content-editor"] .tiptap')
        ->click('[data-test="toolbar-blockquote"]')
        ->wait(0.5)
        ->assertAttributeContains('[data-test="toolbar-blockquote"]', 'class', 'btn-active');
})->group('slow');

it('multiple toolbar commands work sequentially', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="content-editor"] .tiptap')
        ->click('[data-test="toolbar-bold"]')
        ->wait(0.3)
        ->assertAttributeContains('[data-test="toolbar-bold"]', 'class', 'btn-active')
        ->click('[data-test="toolbar-italic"]')
        ->wait(0.3)
        ->assertAttributeContains('[data-test="toolbar-italic"]', 'class', 'btn-active');
})->group('slow');

it('youtube embed dialog opens and serializes as @[youtube] syntax', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="toolbar-youtube"]')
        ->wait(0.5)
        ->fill('[data-test="youtube-url-input"]', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->click('[data-test="youtube-embed-btn"]')
        ->wait(1);

    $value = $page->value('input[name="content"]');

    expect($value)->toContain('@[youtube](');
})->group('slow');

it('typing in editor updates hidden content field', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="content-editor"] .tiptap')
        ->type('[data-test="content-editor"] .tiptap', 'Hello from Tiptap')
        ->wait(0.5);

    $value = $page->value('input[name="content"]');

    expect($value)->toContain('Hello from Tiptap');
})->group('slow');

it('pasting plain markdown renders as formatted content not a code block', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3);

    $page->script('
        const editor = document.querySelector(".tiptap");
        editor.focus();
        const dt = new DataTransfer();
        dt.setData("text/plain", "## Hello World\n\nThis is a paragraph.");
        editor.dispatchEvent(new ClipboardEvent("paste", { clipboardData: dt, bubbles: true }));
    ');

    $page->wait(1);

    $value = $page->value('input[name="content"]');
    expect($value)->toContain('## Hello World')
        ->and($value)->not->toContain('```');
})->group('slow');

it('pasting markdown with single newlines normalizes to separate paragraphs', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3);

    $page->script('
        const editor = document.querySelector(".tiptap");
        editor.focus();
        const dt = new DataTransfer();
        dt.setData("text/plain", "First paragraph.\nSecond paragraph.");
        editor.dispatchEvent(new ClipboardEvent("paste", { clipboardData: dt, bubbles: true }));
    ');

    $page->wait(1);

    $value = $page->value('input[name="content"]');
    expect($value)->toContain('First paragraph.')
        ->and($value)->toContain('Second paragraph.');
})->group('slow');

it('editor height syncs to markdown textarea when switching modes', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3);

    // Set a taller height via Alpine data (simulating a drag resize)
    $page->script("(() => {
        const el = document.querySelector('[data-test=\"content-editor\"]');
        const alpineEl = el ? el.closest('[x-data]') : null;
        if (alpineEl) { Alpine.\$data(alpineEl).editorHeight = '700px'; }
    })()");

    $page->wait(0.5);

    // Switch to markdown mode
    $page->click('[data-test="toolbar-markdown-source"]')
        ->wait(0.5);

    // Assert textarea has the same height via inline style binding
    $height = $page->script("(() => {
        const ta = document.querySelector('textarea[spellcheck=\"false\"]');
        return ta ? ta.style.height : '';
    })()");

    expect($height)->toBe('700px');

    // Switch back to rich text mode
    $page->click('[data-test="toolbar-markdown-source"]')
        ->wait(0.5);

    $editorHeight = $page->script("(() => {
        const el = document.querySelector('[data-test=\"content-editor\"]');
        return el ? el.style.height : '';
    })()");

    expect($editorHeight)->toBe('700px');
})->group('slow');

it('editor height persists across page navigation', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3);

    // Persist a custom height to localStorage so it is restored after navigation
    $page->script("localStorage.setItem('editorHeight', JSON.stringify('650px'))");

    $page->wait(0.3);

    // Navigate away and back
    $page->navigate('/admin/articles')
        ->wait(1)
        ->navigate('/admin/articles/create')
        ->wait(3);

    $editorHeight = $page->script("(() => {
        const el = document.querySelector('[data-test=\"content-editor\"]');
        return el ? el.style.height : '';
    })()");

    expect($editorHeight)->toBe('650px');
})->group('slow');

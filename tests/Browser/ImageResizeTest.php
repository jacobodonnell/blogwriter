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

function loginAndNavigate(string $path): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    return $page->navigate($path);
}

it('image inserted via dialog appears centered in editor', function (): void {
    $page = loginAndNavigate('/admin/articles/create');

    $page->wait(3)
        ->click('[data-test="toolbar-image"]')
        ->wait(0.5)
        ->fill('[data-test="image-url-input"]', 'https://placehold.co/600x400')
        ->fill('[data-test="image-alt-input"]', 'Test image')
        ->click('[data-test="image-insert-btn"]')
        ->wait(2);

    $justifyContent = $page->script(
        "(() => window.getComputedStyle(document.querySelector('[data-resize-container]')).justifyContent)()"
    );

    expect($justifyContent)->toBe('center');
})->group('slow');

it('image resize stores percentage width in markdown', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:50%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');

    $page->wait(3);

    $value = $page->value('input[name="content"]');

    expect($value)->toContain('width:50%');
})->group('slow');

it('image at less than full width is visually narrower than container', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:50%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');

    $page->wait(3);

    $widths = $page->script("(() => {
        const figure = document.querySelector('.ProseMirror figure');
        const editor = document.querySelector('.ProseMirror');
        if (!figure || !editor) return [0, 0];
        return [figure.offsetWidth, editor.offsetWidth];
    })()");

    $figureWidth = $widths[0];
    $editorWidth = $widths[1];

    // Figure should be roughly 50% of editor (within 15% tolerance)
    expect($editorWidth)->toBeGreaterThan(0);
    $ratio = $figureWidth / $editorWidth;
    expect($ratio)->toBeLessThan(0.65)
        ->and($ratio)->toBeGreaterThan(0.35);
})->group('slow');

it('full-width button resets width to null', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:50%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');

    $page->wait(3);

    // Click image to select it
    $page->click('[data-resize-wrapper] img')
        ->wait(0.5);

    // Click full-width button
    $page->click('[data-tip="Full Width"]')
        ->wait(1);

    $value = $page->value('input[name="content"]');

    expect($value)->not->toContain('width:');
})->group('slow');

it('image width renders in server-side preview HTML', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:50%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');

    $page->wait(5);

    // Check preview panel for figure with --figure-width custom property
    $hasFigureWidth = $page->script("(() => {
        const figure = document.querySelector('#preview-panel figure[style*=\"--figure-width\"]');
        return !!figure;
    })()");

    expect($hasFigureWidth)->toBeTrue();
})->group('slow');

it('image cannot be resized beyond editor container width', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:80%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');

    $page->wait(3);

    // Click image to select it and reveal handles
    $page->click('[data-resize-wrapper] img')
        ->wait(0.5);

    // Simulate drag on right handle: mousedown, mousemove +500px outward, mouseup
    $page->script("(() => {
        const handle = document.querySelector('[data-resize-handle=\"right\"]');
        if (!handle) return 'no-handle';
        const rect = handle.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        handle.dispatchEvent(new MouseEvent('mousedown', { clientX: x, clientY: y, bubbles: true }));
        document.dispatchEvent(new MouseEvent('mousemove', { clientX: x + 500, clientY: y, bubbles: true }));
        document.dispatchEvent(new MouseEvent('mouseup', { clientX: x + 500, clientY: y, bubbles: true }));
    })()");

    $page->wait(1);

    $value = $page->value('input[name="content"]');

    // Should either have no width token (full width, >=98%) or a percentage <= 100
    if (str_contains($value, 'width:')) {
        preg_match('/width:(\d+)%/', $value, $matches);
        expect($matches)->not->toBeEmpty();
        expect((int) $matches[1])->toBeLessThanOrEqual(100);
    } else {
        // No width token means full-width — that's correct
        expect($value)->toContain('![](https://placehold.co/600x400');
    }
})->group('slow');

it('committed width matches visual width after resize', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:50%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');
    $page->wait(3);

    $page->click('[data-resize-wrapper] img')->wait(0.5);

    // Read pre-resize widths
    $preWidths = $page->script("(() => {
        const figure = document.querySelector('.ProseMirror figure');
        const pm = document.querySelector('.ProseMirror');
        const style = getComputedStyle(pm);
        const contentWidth = pm.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
        return [figure.offsetWidth, contentWidth];
    })()");

    $preFigureWidth = $preWidths[0];

    // Simulate drag right handle +100px
    $page->script("(() => {
        const handle = document.querySelector('[data-resize-handle=\"right\"]');
        const rect = handle.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        handle.dispatchEvent(new MouseEvent('mousedown', { clientX: x, clientY: y, bubbles: true }));
        document.dispatchEvent(new MouseEvent('mousemove', { clientX: x + 100, clientY: y, bubbles: true }));
        document.dispatchEvent(new MouseEvent('mouseup', { clientX: x + 100, clientY: y, bubbles: true }));
    })()");

    $page->wait(1);

    // Read post-resize figure width
    $postWidth = $page->script("(() => document.querySelector('.ProseMirror figure').offsetWidth)()");

    // Expected: preFigureWidth + 100 (±5px for rounding)
    expect($postWidth)->toBeGreaterThanOrEqual($preFigureWidth + 95)
        ->and($postWidth)->toBeLessThanOrEqual($preFigureWidth + 105);
})->group('slow');

it('image can be resized larger by dragging handle outward', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '![|width:30%](https://placehold.co/600x400)',
    ]);

    $page = loginAndNavigate('/admin/articles/'.$article->id.'/edit');

    $page->wait(3);

    // Click image to select it and reveal handles
    $page->click('[data-resize-wrapper] img')
        ->wait(0.5);

    // Simulate drag on right handle: mousedown, mousemove +200px, mouseup
    $page->script("(() => {
        const handle = document.querySelector('[data-resize-handle=\"right\"]');
        if (!handle) return 'no-handle';
        const rect = handle.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        handle.dispatchEvent(new MouseEvent('mousedown', { clientX: x, clientY: y, bubbles: true }));
        document.dispatchEvent(new MouseEvent('mousemove', { clientX: x + 200, clientY: y, bubbles: true }));
        document.dispatchEvent(new MouseEvent('mouseup', { clientX: x + 200, clientY: y, bubbles: true }));
    })()");

    $page->wait(1);

    $value = $page->value('input[name="content"]');

    // Width should have changed from 30% — either a larger percentage or removed entirely (>=98% = full width)
    if (str_contains($value, 'width:')) {
        preg_match('/width:(\d+)%/', $value, $matches);
        expect($matches)->not->toBeEmpty();
        expect((int) $matches[1])->toBeGreaterThan(30);
    } else {
        // No width means it went to full width (>=98%) — still larger than 30%
        expect($value)->toContain('![](');
    }
})->group('slow');

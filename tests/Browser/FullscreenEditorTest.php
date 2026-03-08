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

function loginToArticle(Article $article, bool $mobile = false): mixed
{
    $page = visit('/login');
    if ($mobile) {
        $page->resize(375, 812);
    }
    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit');
    $page->navigate(route('admin.articles.edit', $article));
    $page->wait(3);

    return $page;
}

function loginAndOpenFullscreenArticle(Article $article, bool $mobile = false): mixed
{
    $page = loginToArticle($article, $mobile);

    $page->click('[data-test="navbar-fullscreen"]')
        ->wait(1);

    return $page;
}

function getCustomizerMode(mixed $page): string
{
    return $page->script("(() => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        return Alpine.\$data(el).mode;
    })()");
}

it('editor content scrolls in fullscreen', function (): void {
    // Create article with lots of content to force overflow
    $longContent = implode("\n\n", array_fill(0, 50, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'));
    $this->article->update(['content' => $longContent]);

    $page = loginAndOpenFullscreenArticle($this->article);

    // Extra wait for Tiptap to render all the markdown content
    $page->wait(2);

    // The scroll container's scrollHeight should exceed its clientHeight
    $dimensions = $page->script("(() => {
        const el = document.querySelector('[data-test=\"fs-scroll-container\"]');
        return [el.scrollHeight, el.clientHeight];
    })()");

    expect($dimensions[0])->toBeGreaterThan($dimensions[1]);

    // Programmatic scroll should work
    $page->script('document.querySelector(\'[data-test="fs-scroll-container"]\').scrollTop = 200');
    $page->wait(0.3);

    $scrollTop = $page->script("(() => {
        const el = document.querySelector('[data-test=\"fs-scroll-container\"]');
        return el.scrollTop;
    })()");

    expect($scrollTop)->toBeGreaterThan(0);
})->group('slow');

it('toolbar stays visible while scrolling', function (): void {
    $longContent = implode("\n\n", array_fill(0, 50, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'));
    $this->article->update(['content' => $longContent]);

    $page = loginAndOpenFullscreenArticle($this->article);

    // Scroll the scroll container
    $page->script('document.querySelector(\'[data-test="fs-scroll-container"]\').scrollTop = 500');

    $page->wait(0.5)
        ->assertVisible('[data-test="fs-toolbar-bold"]')
        ->assertVisible('[data-test="fs-toolbar-settings"]');
})->group('slow');

it('enters fullscreen when Expand clicked', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->assertVisible('[data-test="fs-toolbar-split"]')
        ->assertMissing('[data-test="preview-panel"]');
})->group('slow');

it('collapses to split panel', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->click('[data-test="fs-toolbar-split"]')
        ->wait(1)
        ->assertVisible('[data-test="navbar-fullscreen"]')
        ->assertMissing('[data-test="fs-toolbar-split"]')
        ->assertVisible('[data-test="preview-panel"]');
})->group('slow');

it('collapses to classic editor', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->click('[data-test="fs-toolbar-classic"]')
        ->wait(1)
        ->assertMissing('[data-test="fs-toolbar-classic"]');

    expect(getCustomizerMode($page))->toBe('classic');
})->group('slow');

it('collapses to live preview', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->click('[data-test="fs-toolbar-preview"]')
        ->wait(1)
        ->assertMissing('[data-test="fs-toolbar-preview"]')
        ->assertVisible('[data-test="preview-panel"]');

    expect(getCustomizerMode($page))->toBe('preview');
})->group('slow');

it('exits fullscreen on Escape key', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->assertVisible('[data-test="fs-toolbar-split"]');

    $page->script("window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }))");

    $page->wait(1)
        ->assertMissing('[data-test="fs-toolbar-split"]')
        ->assertVisible('[data-test="navbar-fullscreen"]');
})->group('slow');

it('sidebar fields hidden in fullscreen', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->assertMissing('[data-test="summary-field"]')
        ->assertMissing('[data-test="status-select"]');
})->group('slow');

it('settings slide-over opens in fullscreen', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->click('[data-test="fs-toolbar-settings"]')
        ->wait(0.5)
        ->assertVisible('[data-test="status-select"]');

    // Verify panel is at viewport top (top: 0px in fullscreen)
    $top = $page->script("(() => {
        const panel = document.querySelector('[data-test=\"status-select\"]').closest('.fixed');
        return panel ? getComputedStyle(panel).top : null;
    })()");

    expect($top)->toBe('0px');
})->group('slow');

it('fullscreen preference persists across reload', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article);

    $page->assertVisible('[data-test="fs-toolbar-split"]');

    // Navigate away and back
    $page->navigate(route('admin.articles.edit', $this->article))
        ->wait(3);

    $page->assertVisible('[data-test="fs-toolbar-split"]');
})->group('slow');

it('shows word count in fullscreen', function (): void {
    $this->article->update(['content' => 'One two three four five']);

    $page = loginAndOpenFullscreenArticle($this->article);

    $page->wait(1)
        ->assertSee('5 words');
})->group('slow');

it('shows inline title in fullscreen', function (): void {
    $this->article->update(['title' => 'My Fullscreen Title']);

    $page = loginAndOpenFullscreenArticle($this->article);

    // The inline title textarea should be visible and contain the title
    $titleValue = $page->script("(() => {
        const textareas = document.querySelectorAll('textarea[x-model=\"title\"]');
        for (const ta of textareas) {
            if (ta.offsetParent !== null) return ta.value;
        }
        return null;
    })()");

    expect($titleValue)->toBe('My Fullscreen Title');
})->group('slow');

it('saves article with keyboard shortcut', function (): void {
    $this->article->update(['content' => '## Keyboard Save Test']);

    $page = loginAndOpenFullscreenArticle($this->article);

    // Cmd+S triggers form submission which redirects with flash message
    $page->script("window.dispatchEvent(new KeyboardEvent('keydown', { key: 's', metaKey: true, bubbles: true }))");

    $page->wait(3)
        ->assertSee('Article updated successfully.');
})->group('slow');

it('enters fullscreen on mobile', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article, mobile: true);

    $page->assertVisible('[data-test="fs-toolbar-bold"]')
        ->assertVisible('[data-test="fs-toolbar-split"]')
        ->assertVisible('[data-test="fs-toolbar-preview"]')
        ->assertMissing('[data-test="fs-toolbar-classic"]');
})->group('slow');

it('exits fullscreen on mobile via preview button', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article, mobile: true);

    $page->click('[data-test="fs-toolbar-preview"]')
        ->wait(1);

    expect(getCustomizerMode($page))->toBe('preview');
})->group('slow');

it('opens settings slide-over on mobile fullscreen', function (): void {
    $page = loginAndOpenFullscreenArticle($this->article, mobile: true);

    $page->click('[data-test="fs-toolbar-settings"]')
        ->wait(0.5)
        ->assertVisible('[data-test="status-select"]');
})->group('slow');

it('toggle button switches between editor and preview on mobile', function (): void {
    Setting::set('customizer_editor_mode', 'preview');

    $page = loginToArticle($this->article, mobile: true);

    expect(getCustomizerMode($page))->toBe('preview');

    $page->click('[aria-label="Toggle editor"]')
        ->wait(0.5);
    expect(getCustomizerMode($page))->toBe('split');

    $page->click('[aria-label="Toggle editor"]')
        ->wait(0.5);
    expect(getCustomizerMode($page))->toBe('preview');
})->group('slow');

it('content preserved across fullscreen toggle', function (): void {
    $this->article->update(['content' => '## Test Content Preservation']);

    $page = loginAndOpenFullscreenArticle($this->article);

    // Verify content is present in fullscreen
    $page->assertSeeIn('[data-test="content-editor"]', 'Test Content Preservation');

    // Exit fullscreen via split
    $page->click('[data-test="fs-toolbar-split"]')
        ->wait(1);

    // Content should still be there
    $page->assertSeeIn('[data-test="content-editor"]', 'Test Content Preservation');
})->group('slow');

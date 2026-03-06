<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
    $this->article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Working Draft Title',
        'content' => '## Working Draft Content',
    ]);
    Setting::set('customizer_editor_mode', 'split');
});

function loginAndOpenFullscreenWithRevisions(Article $article): mixed
{
    $page = visit('/login');
    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit');
    $page->navigate(route('admin.articles.edit', $article));

    $page->wait(3)
        ->click('[data-test="navbar-fullscreen"]')
        ->wait(1);

    return $page;
}

it('hides revisions button when no revisions exist', function (): void {
    $page = loginAndOpenFullscreenWithRevisions($this->article);

    $page->assertMissing('[data-test="fs-toolbar-revisions"]');
})->group('slow');

it('shows revisions button when revisions exist', function (): void {
    ArticleRevision::factory()->for($this->article)->create();

    $page = loginAndOpenFullscreenWithRevisions($this->article);

    $page->assertVisible('[data-test="fs-toolbar-revisions"]');
})->group('slow');

it('opens and closes revision panel', function (): void {
    ArticleRevision::factory()->for($this->article)->create();

    $page = loginAndOpenFullscreenWithRevisions($this->article);

    $page->click('[data-test="fs-toolbar-revisions"]')
        ->wait(0.5)
        ->assertSee('Revision History');

    // Close via the X button in the panel
    $page->script("document.querySelector('#revision-panel-target .ph-x').closest('button').click()");

    $page->wait(0.5)
        ->assertDontSee('Revision History');
})->group('slow');

it('previews a revision in read-only mode', function (): void {
    ArticleRevision::factory()->for($this->article)->create([
        'title' => 'Revision Title',
        'content' => '## Revision Content Here',
    ]);

    $page = loginAndOpenFullscreenWithRevisions($this->article);

    $page->click('[data-test="fs-toolbar-revisions"]')
        ->wait(1);

    // Click the first revision via Alpine data (async — AJAX fetch)
    $page->script("(async () => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        await Alpine.\$data(el).previewRevision(0);
    })()");

    $page->wait(2)
        ->assertSee('Viewing revision');

    // Editor should have the warning ring class
    $hasWarningRing = $page->script("(() => {
        const el = document.querySelector('[data-test=\"content-editor\"]');
        return el.classList.contains('ring-warning/30');
    })()");

    expect($hasWarningRing)->toBeTrue();
})->group('slow');

it('exits revision preview and restores working content', function (): void {
    ArticleRevision::factory()->for($this->article)->create([
        'title' => 'Old Revision Title',
        'content' => '## Old Revision Content',
    ]);

    $page = loginAndOpenFullscreenWithRevisions($this->article);

    // Open panel and preview revision
    $page->click('[data-test="fs-toolbar-revisions"]')
        ->wait(1);

    $page->script("(async () => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        await Alpine.\$data(el).previewRevision(0);
    })()");
    $page->wait(2);

    // Click "Current version" to exit preview
    $page->script("(() => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        Alpine.\$data(el).exitRevisionBrowsing();
    })()");
    $page->wait(0.5);

    // Warning should be gone
    $page->assertDontSee('Viewing revision');

    // Title should be restored to working draft
    $titleValue = $page->script("(() => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        return Alpine.\$data(el).title;
    })()");

    expect($titleValue)->toBe('Working Draft Title');
})->group('slow');

it('restores a revision to working draft', function (): void {
    ArticleRevision::factory()->for($this->article)->create([
        'title' => 'Restored Revision Title',
        'content' => '## Restored Revision Content',
    ]);

    $page = loginAndOpenFullscreenWithRevisions($this->article);

    // Open panel and preview revision
    $page->click('[data-test="fs-toolbar-revisions"]')
        ->wait(1);

    $page->script("(async () => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        await Alpine.\$data(el).previewRevision(0);
    })()");
    $page->wait(2);

    // Click "Restore this version"
    $page->script("(async () => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        await Alpine.\$data(el).restoreRevision(0);
    })()");
    $page->wait(1);

    // Warning should be gone (no longer browsing)
    $page->assertDontSee('Viewing revision');

    // Title should now be the revision title
    $titleValue = $page->script("(() => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        return Alpine.\$data(el).title;
    })()");

    expect($titleValue)->toBe('Restored Revision Title');

    // Editor should be editable again
    $editable = $page->script("(() => {
        const el = document.querySelector('[data-test=\"content-editor\"] .tiptap');
        return el?.getAttribute('contenteditable');
    })()");

    expect($editable)->toBe('true');
})->group('slow');

it('displays revision titles and timestamps', function (): void {
    ArticleRevision::factory()->for($this->article)->create([
        'title' => 'My First Revision',
        'created_at' => now()->subHour(),
    ]);

    $page = loginAndOpenFullscreenWithRevisions($this->article);

    $page->click('[data-test="fs-toolbar-revisions"]')
        ->wait(0.5)
        ->assertSee('My First Revision')
        ->assertSee('1 hour ago');
})->group('slow');

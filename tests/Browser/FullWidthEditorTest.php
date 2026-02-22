<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
    $this->article = Article::factory()->draft()->for($this->user)->create();
});

function loginAndOpenArticle(Article $article): mixed
{
    $page = visit('/login');
    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit');
    $page->navigate(route('admin.articles.edit', $article));

    return $page;
}

it('shows full-width toggle button when drawer is open', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->assertVisible('[aria-label="Toggle full width editor"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('enters full-width mode and hides preview panel', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')
        ->assertMissing('[data-test="preview-panel"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('hides viewport preset buttons in full-width mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')
        ->assertMissing('[data-tip="Phone (375px)"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('shows two-column layout in full-width mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')
        ->assertVisible('[data-test="status-select"]')
        ->assertVisible('[data-test="content-editor"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('persists full-width preference across reload', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]');
    $page->navigate(route('admin.articles.edit', $this->article));

    $page->assertMissing('[data-tip="Phone (375px)"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('closes drawer and exits full-width mode when close editor is clicked', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')
        ->assertMissing('[data-tip="Phone (375px)"]');   // confirm full-width active

    $page->click('[aria-label="Toggle editor"]')          // close drawer
        ->assertVisible('[data-tip="Phone (375px)"]')    // viewport presets back
        ->assertNoJavaScriptErrors();
})->group('slow');

it('does not restore full-width when reopening drawer after close', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')  // enter full-width
        ->click('[aria-label="Toggle editor"]')              // close (resets fullWidth)
        ->click('[aria-label="Toggle editor"]');             // reopen

    $page->assertVisible('[data-tip="Phone (375px)"]')       // viewport presets still visible
        ->assertNoJavaScriptErrors();
})->group('slow');

it('shows summary field in main column in full-width mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')
        ->assertVisible('[data-test="summary-field"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('shows save button at top of sidebar in full-width mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle full width editor"]')
        ->assertVisible('[data-test="save-button"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

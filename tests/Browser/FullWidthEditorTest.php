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

it('shows classic editor toggle button when drawer is open', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->assertVisible('[aria-label="Toggle classic editor"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('enters classic editor mode and hides preview panel', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')
        ->assertMissing('[data-test="preview-panel"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('hides viewport preset buttons in classic editor mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')
        ->assertMissing('[data-tip="Phone (375px)"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('shows two-column layout in classic editor mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')
        ->assertVisible('[data-test="status-select"]')
        ->assertVisible('[data-test="content-editor"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('persists classic editor preference across reload', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]');
    $page->navigate(route('admin.articles.edit', $this->article));

    $page->assertMissing('[data-tip="Phone (375px)"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('closes drawer and exits classic editor mode when close editor is clicked', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')
        ->assertMissing('[data-tip="Phone (375px)"]');   // confirm classic editor active

    $page->click('[aria-label="Toggle editor"]')          // close drawer
        ->assertVisible('[data-tip="Phone (375px)"]')    // viewport presets back
        ->assertNoJavaScriptErrors();
})->group('slow');

it('does not restore classic editor when reopening drawer after close', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')  // enter classic editor
        ->click('[aria-label="Toggle editor"]')              // close (resets classicEditor)
        ->click('[aria-label="Toggle editor"]');             // reopen

    $page->assertVisible('[data-tip="Phone (375px)"]')       // viewport presets still visible
        ->assertNoJavaScriptErrors();
})->group('slow');

it('shows summary field in main column in classic editor mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')
        ->assertVisible('[data-test="summary-field"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

it('shows save button at top of sidebar in classic editor mode', function (): void {
    $page = loginAndOpenArticle($this->article);

    $page->click('[aria-label="Toggle classic editor"]')
        ->assertVisible('[data-test="save-button"]')
        ->assertNoJavaScriptErrors();
})->group('slow');

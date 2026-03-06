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
});

function customizerState(mixed $page, string $property): mixed
{
    return $page->script("(() => {
        const el = document.querySelector('[x-ref=\"customizerForm\"]').closest('[x-data]');
        return Alpine.\$data(el).{$property};
    })()");
}

function loginToEditor(Article $article): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate(route('admin.articles.edit', $article));
    $page->wait(3);

    return $page;
}

it('flushes localStorage when server default changes', function (): void {
    Setting::set('customizer_editor_mode', 'split');

    $page = loginToEditor($this->article);

    // Verify localStorage has the server mode stored
    $storedMode = $page->script("(() => localStorage.getItem('customizerServerEditorMode'))()");
    expect($storedMode)->toBe('split');

    // Simulate user switching to fullscreen via localStorage
    $page->script("localStorage.setItem('customizerMode', 'fullscreen')");

    // Change server default to classic
    Setting::set('customizer_editor_mode', 'classic');

    // Reload the editor page so customizerLayout re-initializes
    $page->navigate(route('admin.articles.edit', $this->article));
    $page->wait(3);

    // The server mode key should now reflect classic
    $storedMode = $page->script("(() => localStorage.getItem('customizerServerEditorMode'))()");
    expect($storedMode)->toBe('classic');

    // After flush, Alpine initializes to the new server default
    expect(customizerState($page, 'mode'))->toBe('classic');
})->group('slow');

it('opens in classic editor after changing default to classic', function (): void {
    Setting::set('customizer_editor_mode', 'split');

    $page = loginToEditor($this->article);

    // Simulate prior Preview mode
    $page->script("localStorage.setItem('customizerMode', 'preview')");

    // Change server default to classic
    Setting::set('customizer_editor_mode', 'classic');

    // Navigate to editor so customizerLayout re-initializes
    $page->navigate(route('admin.articles.edit', $this->article));
    $page->wait(3);

    // Assert Alpine state reflects classic mode
    expect(customizerState($page, 'mode'))->toBe('classic');
})->group('slow');

it('opens in split panel after changing default to split', function (): void {
    Setting::set('customizer_editor_mode', 'fullscreen');

    $page = loginToEditor($this->article);

    // Simulate prior Preview mode
    $page->script("localStorage.setItem('customizerMode', 'preview')");

    // Change server default to split
    Setting::set('customizer_editor_mode', 'split');

    // Navigate to editor so customizerLayout re-initializes
    $page->navigate(route('admin.articles.edit', $this->article));
    $page->wait(3);

    // Assert Alpine state reflects split mode
    expect(customizerState($page, 'mode'))->toBe('split');

    // Assert preview panel is visible
    $page->assertVisible('[data-test="preview-panel"]');
})->group('slow');

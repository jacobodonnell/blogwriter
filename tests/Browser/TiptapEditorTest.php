<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginToAdmin(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    return $page;
}

it('loads editor on create page without JS errors', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->assertNoJavaScriptErrors();
})->group('slow');

it('loads editor on edit page with existing content without JS errors', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => "## Hello World\n\nThis is a **test** article with markdown content.",
    ]);

    $page = loginToAdmin();

    $page->navigate('/admin/articles/'.$article->id.'/edit')
        ->wait(3)
        ->assertNoJavaScriptErrors();
})->group('slow');

it('bold toolbar button works without JS errors', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="toolbar-bold"]')
        ->wait(0.5)
        ->assertNoJavaScriptErrors();
})->group('slow');

it('h2 toolbar button works without JS errors', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="toolbar-h2"]')
        ->wait(0.5)
        ->assertNoJavaScriptErrors();
})->group('slow');

it('blockquote toolbar button works without JS errors', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="toolbar-blockquote"]')
        ->wait(0.5)
        ->assertNoJavaScriptErrors();
})->group('slow');

it('multiple toolbar commands work sequentially without JS errors', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="toolbar-bold"]')
        ->wait(0.3)
        ->click('[data-test="toolbar-italic"]')
        ->wait(0.3)
        ->click('[data-test="toolbar-bullet-list"]')
        ->wait(0.3)
        ->click('[data-test="toolbar-ordered-list"]')
        ->wait(0.3)
        ->assertNoJavaScriptErrors();
})->group('slow');

it('youtube embed dialog opens and works without JS errors', function (): void {
    $page = loginToAdmin();

    $page->navigate('/admin/articles/create')
        ->wait(3)
        ->click('[data-test="toolbar-youtube"]')
        ->wait(0.5)
        ->fill('[data-test="youtube-url-input"]', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->click('[data-test="youtube-embed-btn"]')
        ->wait(0.5)
        ->assertNoJavaScriptErrors();
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

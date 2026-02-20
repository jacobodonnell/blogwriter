<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginForThemeTest(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    return $page;
}

it('applies dark theme before Alpine initializes when localStorage is set to dark', function (): void {
    $page = loginForThemeTest();

    // Set dark mode preference
    $page->script("localStorage.setItem('themeMode', 'dark')");

    // Navigate to admin dashboard — theme should be set by inline script before Alpine
    $page->navigate('/admin/dashboard');

    $page->assertScript('document.documentElement.getAttribute("data-theme")', 'dracula');
})->group('slow');

it('applies light theme before Alpine initializes when localStorage is set to light', function (): void {
    $page = loginForThemeTest();

    // Set light mode preference
    $page->script("localStorage.setItem('themeMode', 'light')");

    // Navigate to admin dashboard
    $page->navigate('/admin/dashboard');

    $page->assertScript('document.documentElement.getAttribute("data-theme")', 'lofi');
})->group('slow');

it('respects system dark preference when themeMode is system', function (): void {
    // Use dark mode emulation from the start — login and set system preference
    $page = visit('/login')->inDarkMode();

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->script("localStorage.setItem('themeMode', 'system')");
    $page->navigate('/admin/dashboard');

    $page->assertScript('document.documentElement.getAttribute("data-theme")', 'dracula');
})->group('slow');

it('does not flash wrong theme on navigation', function (): void {
    $page = loginForThemeTest();

    // Set dark mode preference
    $page->script("localStorage.setItem('themeMode', 'dark')");

    // Navigate between pages and verify theme stays consistent
    $page->navigate('/admin/dashboard');
    $page->assertScript('document.documentElement.getAttribute("data-theme")', 'dracula');

    $page->navigate('/admin/settings/appearance');
    $page->assertScript('document.documentElement.getAttribute("data-theme")', 'dracula');

    $page->navigate('/admin/dashboard');
    $page->assertScript('document.documentElement.getAttribute("data-theme")', 'dracula');
})->group('slow');

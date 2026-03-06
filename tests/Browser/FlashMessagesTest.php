<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginAndVisitAppearanceSettings(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/settings/appearance');

    return $page;
}

it('shows a dismissible success toast after saving appearance settings', function (): void {
    $page = loginAndVisitAppearanceSettings();

    $page->click('[data-test="save-appearance"]')
        ->wait(0.5)
        ->assertVisible('[data-test="session-toast"]')
        ->click('[data-test="session-toast"] [data-test="toast-dismiss"]')
        ->wait(0.5)
        ->assertMissing('[data-test="session-toast"]');
})->group('slow');

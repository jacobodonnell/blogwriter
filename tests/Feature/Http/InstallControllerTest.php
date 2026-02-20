<?php

declare(strict_types=1);

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

it('shows cli install page when not installed', function (): void {
    @unlink(storage_path('installed.lock'));

    $response = $this->get('/install');

    $response->assertSuccessful()
        ->assertSee('php artisan blogwriter:install');
});

it('redirects to admin when already installed', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $response = $this->get('/install');

    $response->assertRedirect(route('admin.dashboard'));
});

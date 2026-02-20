<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('loads site settings page for authenticated user', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.site'))
        ->assertSuccessful()
        ->assertSee('Site Information')
        ->assertSee('Default Image')
        ->assertSee('Page Subtitles')
        ->assertSee('Environment');
});

it('redirects unauthenticated users from site settings', function (): void {
    $this->get(route('admin.settings.site'))
        ->assertRedirect(route('login'));
});

it('shows tab navigation with all three tabs', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.site'))
        ->assertSuccessful()
        ->assertSee('Profile')
        ->assertSee('Site')
        ->assertSee('Appearance');
});

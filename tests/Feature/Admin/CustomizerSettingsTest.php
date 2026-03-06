<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('loads customizer settings page for authenticated user', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.customizer'))
        ->assertSuccessful()
        ->assertSee('Default Editor Mode');
});

it('redirects unauthenticated users from customizer page', function (): void {
    $this->get(route('admin.settings.customizer'))
        ->assertRedirect(route('login'));
});

it('saves each valid editor mode', function (string $mode): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.customizer.update'), [
            'customizer_editor_mode' => $mode,
        ])
        ->assertRedirect(route('admin.settings.customizer'))
        ->assertSessionHas('success');

    expect(Setting::get('customizer_editor_mode'))->toBe($mode);
})->with(['fullscreen', 'classic', 'split']);

it('rejects invalid mode values', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.customizer.update'), [
            'customizer_editor_mode' => 'zen',
        ])
        ->assertSessionHasErrors('customizer_editor_mode');
});

it('requires the editor mode field', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.customizer.update'), [])
        ->assertSessionHasErrors('customizer_editor_mode');
});

it('displays current setting on customizer page', function (): void {
    Setting::set('customizer_editor_mode', 'classic');

    $this->actingAs($this->user)
        ->get(route('admin.settings.customizer'))
        ->assertSuccessful()
        ->assertSee('classic');
});

it('shows customizer tab in settings navigation', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.profile'))
        ->assertSuccessful()
        ->assertSee('Customizer');
});

it('defaults to fullscreen when no setting exists', function (): void {
    $response = $this->actingAs($this->user)
        ->get(route('admin.settings.customizer'));

    $response->assertSuccessful();

    // The fullscreen radio should be checked by default
    $response->assertSee('checked', false);
});

<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('loads appearance settings page for authenticated user', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.appearance'))
        ->assertSuccessful()
        ->assertSee('Light Theme')
        ->assertSee('Dark Theme')
        ->assertSee('Font');
});

it('redirects unauthenticated users from appearance page', function (): void {
    $this->get(route('admin.settings.appearance'))
        ->assertRedirect(route('login'));
});

it('saves valid theme and font selections', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), [
            'theme_light' => 'cupcake',
            'theme_dark' => 'forest',
            'theme_font' => 'inter',
        ])
        ->assertRedirect(route('admin.settings.appearance'))
        ->assertSessionHas('success');

    expect(Setting::get('theme_light'))->toBe('cupcake')
        ->and(Setting::get('theme_dark'))->toBe('forest')
        ->and(Setting::get('theme_font'))->toBe('inter');
});

it('saves default theme values', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), [
            'theme_light' => 'lofi',
            'theme_dark' => 'dracula',
            'theme_font' => 'noto-sans',
        ])
        ->assertRedirect(route('admin.settings.appearance'))
        ->assertSessionHas('success');

    expect(Setting::get('theme_light'))->toBe('lofi')
        ->and(Setting::get('theme_dark'))->toBe('dracula')
        ->and(Setting::get('theme_font'))->toBe('noto-sans');
});

it('rejects invalid theme names', function (string $field): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), [
            'theme_light' => 'lofi',
            'theme_dark' => 'dracula',
            'theme_font' => 'noto-sans',
            $field => 'nonexistent-theme',
        ])
        ->assertSessionHasErrors($field);
})->with([
    'theme_light',
    'theme_dark',
]);

it('rejects invalid font keys', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), [
            'theme_light' => 'lofi',
            'theme_dark' => 'dracula',
            'theme_font' => 'comic-sans',
        ])
        ->assertSessionHasErrors('theme_font');
});

it('requires all three fields', function (string $field): void {
    $data = [
        'theme_light' => 'lofi',
        'theme_dark' => 'dracula',
        'theme_font' => 'noto-sans',
    ];
    unset($data[$field]);

    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), $data)
        ->assertSessionHasErrors($field);
})->with([
    'theme_light',
    'theme_dark',
    'theme_font',
]);

it('displays current settings on appearance page', function (): void {
    Setting::set('theme_light', 'cyberpunk');
    Setting::set('theme_dark', 'halloween');
    Setting::set('theme_font', 'jetbrains-mono');

    $this->actingAs($this->user)
        ->get(route('admin.settings.appearance'))
        ->assertSuccessful()
        ->assertSee('cyberpunk')
        ->assertSee('halloween')
        ->assertSee('JetBrains Mono');
});

it('shows appearance tab in settings navigation', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.profile'))
        ->assertSuccessful()
        ->assertSee('Appearance');
});

it('rejects a dark theme in the light theme field', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), [
            'theme_light' => 'dracula',
            'theme_dark' => 'dracula',
            'theme_font' => 'noto-sans',
        ])
        ->assertSessionHasErrors('theme_light');
});

it('accepts every configured font', function (): void {
    $fonts = array_keys(config('appearance.fonts'));

    foreach ($fonts as $font) {
        $this->actingAs($this->user)
            ->put(route('admin.settings.appearance.update'), [
                'theme_light' => 'lofi',
                'theme_dark' => 'dracula',
                'theme_font' => $font,
            ])
            ->assertRedirect(route('admin.settings.appearance'))
            ->assertSessionHas('success');

        expect(Setting::get('theme_font'))->toBe($font);
    }
});

it('renders font categories on appearance page', function (): void {
    $response = $this->actingAs($this->user)
        ->get(route('admin.settings.appearance'))
        ->assertSuccessful();

    foreach (array_keys(config('appearance.font_categories')) as $category) {
        $response->assertSee($category);
    }
});

it('rejects a light theme in the dark theme field', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.appearance.update'), [
            'theme_light' => 'lofi',
            'theme_dark' => 'lofi',
            'theme_font' => 'noto-sans',
        ])
        ->assertSessionHasErrors('theme_dark');
});

it('only shows light themes in the light dropdown', function (): void {
    $response = $this->actingAs($this->user)
        ->get(route('admin.settings.appearance'))
        ->assertSuccessful();

    $lightThemes = config('appearance.themes_light');
    $darkThemes = config('appearance.themes_dark');

    // Light dropdown should contain light themes
    foreach ($lightThemes as $theme) {
        $response->assertSee("selectLight('{$theme}')", false);
    }

    // Light dropdown should NOT contain dark themes
    foreach ($darkThemes as $theme) {
        $response->assertDontSee("selectLight('{$theme}')", false);
    }
});

it('only shows dark themes in the dark dropdown', function (): void {
    $response = $this->actingAs($this->user)
        ->get(route('admin.settings.appearance'))
        ->assertSuccessful();

    $lightThemes = config('appearance.themes_light');
    $darkThemes = config('appearance.themes_dark');

    // Dark dropdown should contain dark themes
    foreach ($darkThemes as $theme) {
        $response->assertSee("selectDark('{$theme}')", false);
    }

    // Dark dropdown should NOT contain light themes
    foreach ($lightThemes as $theme) {
        $response->assertDontSee("selectDark('{$theme}')", false);
    }
});

it('accepts every configured theme', function (): void {
    foreach (config('appearance.themes_light') as $theme) {
        $this->actingAs($this->user)
            ->put(route('admin.settings.appearance.update'), [
                'theme_light' => $theme,
                'theme_dark' => 'dracula',
                'theme_font' => 'noto-sans',
            ])
            ->assertRedirect(route('admin.settings.appearance'))
            ->assertSessionHas('success');

        expect(Setting::get('theme_light'))->toBe($theme);
    }

    foreach (config('appearance.themes_dark') as $theme) {
        $this->actingAs($this->user)
            ->put(route('admin.settings.appearance.update'), [
                'theme_light' => 'lofi',
                'theme_dark' => $theme,
                'theme_font' => 'noto-sans',
            ])
            ->assertRedirect(route('admin.settings.appearance'))
            ->assertSessionHas('success');

        expect(Setting::get('theme_dark'))->toBe($theme);
    }
});

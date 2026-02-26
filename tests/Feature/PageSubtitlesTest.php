<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;

it('shows home subtitle when setting exists', function (): void {
    Setting::set('page_home_subtitle', 'Welcome to my blog');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Welcome to my blog');
});

it('does not show home subtitle when setting is empty', function (): void {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertViewHas('subtitle', '');
});

it('shows articles subtitle when setting exists', function (): void {
    Setting::set('page_articles_subtitle', 'Thoughts and ideas');

    $this->get('/articles')
        ->assertSuccessful()
        ->assertSee('Thoughts and ideas');
});

it('does not show articles subtitle when setting is empty', function (): void {
    $response = $this->get('/articles');

    $response->assertSuccessful();
    $response->assertViewHas('subtitle', '');
});

it('shows photos subtitle when setting exists', function (): void {
    Setting::set('page_photos_subtitle', 'Captured moments');

    $this->get('/photos')
        ->assertSuccessful()
        ->assertSee('Captured moments');
});

it('does not show photos subtitle when setting is empty', function (): void {
    $response = $this->get('/photos');

    $response->assertSuccessful();
    $response->assertViewHas('subtitle', '');
});

it('saves page subtitles as authenticated user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.settings.site.update'), [
            'page_home_subtitle' => 'My home subtitle',
            'page_articles_subtitle' => 'My articles subtitle',
            'page_photos_subtitle' => 'My photos subtitle',
        ])
        ->assertRedirect(route('admin.settings.site'));

    expect(Setting::get('page_home_subtitle'))->toBe('My home subtitle');
    expect(Setting::get('page_articles_subtitle'))->toBe('My articles subtitle');
    expect(Setting::get('page_photos_subtitle'))->toBe('My photos subtitle');
});

it('requires authentication to update page subtitles', function (): void {
    $this->put(route('admin.settings.site.update'), [
        'page_home_subtitle' => 'Test',
    ])->assertRedirect(route('login'));
});

it('validates subtitle max length', function (string $field): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.settings.site.update'), [
            $field => str_repeat('a', 501),
        ])
        ->assertSessionHasErrors($field);
})->with([
    'page_home_subtitle',
    'page_articles_subtitle',
    'page_photos_subtitle',
]);

it('deletes setting when subtitle is cleared', function (): void {
    $user = User::factory()->create();
    Setting::set('page_home_subtitle', 'Old subtitle');

    $this->actingAs($user)
        ->put(route('admin.settings.site.update'), [
            'page_home_subtitle' => '',
            'page_articles_subtitle' => '',
            'page_photos_subtitle' => '',
        ])
        ->assertRedirect(route('admin.settings.site'));

    expect(Setting::get('page_home_subtitle'))->toBeNull();
});

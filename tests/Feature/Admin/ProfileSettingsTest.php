<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
    ]);
});

it('redirects unauthenticated users from settings update', function (): void {
    $this->put(route('admin.settings.profile.update'), [
        'profile_name' => 'New Name',
    ])->assertRedirect(route('login'));
});

it('displays settings page with current profile data in form', function (): void {
    $this->user->update(['name' => 'Jane Doe']);
    Setting::set('profile_bio', 'A writer.');
    Setting::set('profile_github', 'https://github.com/janedoe');

    $this->actingAs($this->user)
        ->get(route('admin.settings'))
        ->assertSuccessful()
        ->assertSee('Jane Doe')
        ->assertSee('A writer.')
        ->assertSee('https://github.com/janedoe');
});

it('can save profile settings via form', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.profile.update'), [
            'profile_name' => 'Updated Name',
            'profile_bio' => 'Updated bio.',
            'profile_avatar' => 'https://example.com/avatar.jpg',
            'profile_github' => 'https://github.com/updated',
            'profile_mastodon' => 'https://mastodon.social/@updated',
            'profile_bluesky' => 'https://bsky.app/profile/updated',
            'profile_email' => 'updated@example.com',
        ])
        ->assertRedirect(route('admin.settings'))
        ->assertSessionHas('profile_success');

    expect($this->user->fresh()->name)->toBe('Updated Name')
        ->and(Setting::get('profile_bio'))->toBe('Updated bio.')
        ->and(Setting::get('profile_avatar'))->toBe('https://example.com/avatar.jpg')
        ->and(Setting::get('profile_github'))->toBe('https://github.com/updated')
        ->and(Setting::get('profile_email'))->toBe('updated@example.com');
});

it('requires profile_name', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.profile.update'), [
            'profile_name' => '',
        ])
        ->assertSessionHasErrors('profile_name');
});

it('validates url fields', function (string $field): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.profile.update'), [
            'profile_name' => 'Valid Name',
            $field => 'not-a-url',
        ])
        ->assertSessionHasErrors($field);
})->with([
    'profile_avatar',
    'profile_github',
    'profile_mastodon',
    'profile_bluesky',
]);

it('validates email field', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.profile.update'), [
            'profile_name' => 'Valid Name',
            'profile_email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('profile_email');
});

it('clears settings when fields are empty', function (): void {
    Setting::set('profile_bio', 'Old bio.');
    Setting::set('profile_github', 'https://github.com/old');

    $this->actingAs($this->user)
        ->put(route('admin.settings.profile.update'), [
            'profile_name' => 'Still Here',
            'profile_bio' => '',
            'profile_github' => '',
        ])
        ->assertRedirect(route('admin.settings'));

    expect($this->user->fresh()->name)->toBe('Still Here')
        ->and(Setting::get('profile_bio'))->toBeNull()
        ->and(Setting::get('profile_github'))->toBeNull();
});

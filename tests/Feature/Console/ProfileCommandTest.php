<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('sets profile settings non-interactively', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => 'Jane Doe',
        '--bio' => 'A writer and developer.',
        '--github' => 'https://github.com/janedoe',
        '--mastodon' => 'https://mastodon.social/@janedoe',
        '--bluesky' => 'https://bsky.app/profile/janedoe',
        '--email' => 'jane@example.com',
    ])->assertSuccessful();

    expect($this->user->fresh()->name)->toBe('Jane Doe')
        ->and(Setting::get('profile_bio'))->toBe('A writer and developer.')
        ->and(Setting::get('profile_github'))->toBe('https://github.com/janedoe')
        ->and(Setting::get('profile_mastodon'))->toBe('https://mastodon.social/@janedoe')
        ->and(Setting::get('profile_bluesky'))->toBe('https://bsky.app/profile/janedoe')
        ->and(Setting::get('profile_email'))->toBe('jane@example.com');
});

it('validates URLs in non-interactive mode', function (): void {
    $this->artisan('blogwriter:profile', [
        '--github' => 'not-a-url',
    ])->assertFailed();
});

it('validates email in non-interactive mode', function (): void {
    $this->artisan('blogwriter:profile', [
        '--email' => 'not-an-email',
    ])->assertFailed();
});

it('defaults name to current user name', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => '',
    ])->assertSuccessful();

    expect($this->user->fresh()->name)->toBe('Test User');
});

it('defaults email to current user email', function (): void {
    $this->artisan('blogwriter:profile', [
        '--email' => '',
    ])->assertSuccessful();

    expect(Setting::get('profile_email'))->toBe('test@example.com');
});

it('displays summary table after saving', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => 'Jane Doe',
        '--bio' => 'Hello world.',
    ])
        ->expectsOutputToContain('Jane Doe')
        ->expectsOutputToContain('Hello world.')
        ->assertSuccessful();
});

it('saves name to users table', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => 'Saved User',
        '--bio' => 'Persisted bio.',
    ])->assertSuccessful();

    expect($this->user->fresh()->name)->toBe('Saved User');

    $this->assertDatabaseHas('settings', [
        'key' => 'profile_bio',
        'value' => 'Persisted bio.',
    ]);

    $this->assertDatabaseMissing('settings', [
        'key' => 'profile_name',
    ]);
});

it('updates existing settings', function (): void {
    Setting::set('profile_bio', 'Old Bio');

    $this->artisan('blogwriter:profile', [
        '--name' => 'New Name',
    ])->assertSuccessful();

    expect($this->user->fresh()->name)->toBe('New Name');
});

it('skips empty optional fields without saving them', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => 'Jane Doe',
    ])->assertSuccessful();

    expect($this->user->fresh()->name)->toBe('Jane Doe');
    $this->assertDatabaseMissing('settings', ['key' => 'profile_github']);
});

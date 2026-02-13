<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
        '--twitter' => 'https://twitter.com/janedoe',
        '--email' => 'jane@example.com',
        '--website' => 'https://janedoe.com',
    ])->assertSuccessful();

    expect(Setting::get('profile_name'))->toBe('Jane Doe')
        ->and(Setting::get('profile_bio'))->toBe('A writer and developer.')
        ->and(Setting::get('social_github'))->toBe('https://github.com/janedoe')
        ->and(Setting::get('social_twitter'))->toBe('https://twitter.com/janedoe')
        ->and(Setting::get('social_email'))->toBe('jane@example.com')
        ->and(Setting::get('social_website'))->toBe('https://janedoe.com');
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

    expect(Setting::get('profile_name'))->toBe('Test User');
});

it('defaults email to current user email', function (): void {
    $this->artisan('blogwriter:profile', [
        '--email' => '',
    ])->assertSuccessful();

    expect(Setting::get('social_email'))->toBe('test@example.com');
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

it('saves settings to database', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => 'Saved User',
        '--bio' => 'Persisted bio.',
    ])->assertSuccessful();

    $this->assertDatabaseHas('settings', [
        'key' => 'profile_name',
        'value' => 'Saved User',
    ]);

    $this->assertDatabaseHas('settings', [
        'key' => 'profile_bio',
        'value' => 'Persisted bio.',
    ]);
});

it('updates existing settings', function (): void {
    Setting::set('profile_name', 'Old Name');

    $this->artisan('blogwriter:profile', [
        '--name' => 'New Name',
    ])->assertSuccessful();

    expect(Setting::get('profile_name'))->toBe('New Name');
    expect(Setting::query()->where('key', 'profile_name')->count())->toBe(1);
});

it('skips empty optional fields without saving them', function (): void {
    $this->artisan('blogwriter:profile', [
        '--name' => 'Jane Doe',
    ])->assertSuccessful();

    expect(Setting::get('profile_name'))->toBe('Jane Doe');
    $this->assertDatabaseMissing('settings', ['key' => 'social_github']);
});

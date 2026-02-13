<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
    ]);
});

it('displays profile page with settings data', function (): void {
    Setting::set('profile_name', 'Jane Doe');
    Setting::set('profile_bio', 'IndieWeb enthusiast and writer.');
    Setting::set('profile_github', 'https://github.com/janedoe');
    Setting::set('profile_mastodon', 'https://mastodon.social/@janedoe');
    Setting::set('profile_bluesky', 'https://bsky.app/profile/janedoe');
    Setting::set('profile_email', 'jane@example.com');

    $response = $this->get('/profile');

    $response->assertSuccessful()
        ->assertSee('Jane Doe')
        ->assertSee('IndieWeb enthusiast and writer.')
        ->assertSee('https://github.com/janedoe')
        ->assertSee('https://mastodon.social/@janedoe')
        ->assertSee('https://bsky.app/profile/janedoe')
        ->assertSee('jane@example.com');
});

it('displays default values when no settings exist', function (): void {
    $response = $this->get('/profile');

    $response->assertSuccessful()
        ->assertSee('Test Author');
});

it('renders h-card microformat markup', function (): void {
    Setting::set('profile_name', 'Jane Doe');
    Setting::set('profile_bio', 'A short bio.');

    $response = $this->get('/profile');

    $response->assertSuccessful()
        ->assertSee('h-card', false)
        ->assertSee('p-name', false)
        ->assertSee('p-note', false)
        ->assertSee('u-url', false);
});

it('conditionally shows social links', function (): void {
    // No social links set — should not show Connect section
    $response = $this->get('/profile');
    $response->assertSuccessful()
        ->assertDontSee('Connect');

    // Set one social link
    Setting::set('profile_github', 'https://github.com/janedoe');

    $response = $this->get('/profile');
    $response->assertSuccessful()
        ->assertSee('Connect')
        ->assertSee('GitHub');
});

it('layout h-card uses settings data', function (): void {
    Setting::set('profile_name', 'Layout Author');
    Setting::set('profile_bio', 'Layout bio text.');
    Setting::set('profile_github', 'https://github.com/layoutauthor');

    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Layout Author')
        ->assertSee('Layout bio text.')
        ->assertSee('https://github.com/layoutauthor');
});

it('layout h-card falls back to user name when no setting exists', function (): void {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Test Author');
});

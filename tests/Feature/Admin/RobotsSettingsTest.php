<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('loads the robots settings page for authenticated users', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.robots'))
        ->assertSuccessful()
        ->assertSee('Robots.txt');
});

it('redirects unauthenticated users', function (): void {
    $this->get(route('admin.settings.robots'))
        ->assertRedirect(route('login'));
});

it('can save robots.txt content', function (): void {
    $content = "User-agent: *\nDisallow: /admin";

    $this->actingAs($this->user)
        ->put(route('admin.settings.robots.update'), ['robots_txt' => $content])
        ->assertRedirect(route('admin.settings.robots'))
        ->assertSessionHas('success');

    expect(Setting::get('robots_txt'))->toBe($content);
});

it('requires robots_txt field', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.robots.update'), ['robots_txt' => ''])
        ->assertSessionHasErrors('robots_txt');
});

it('rejects content exceeding 10000 characters', function (): void {
    $this->actingAs($this->user)
        ->put(route('admin.settings.robots.update'), ['robots_txt' => str_repeat('a', 10001)])
        ->assertSessionHasErrors('robots_txt');
});

it('serves robots.txt publicly', function (): void {
    $this->get('/robots.txt')
        ->assertSuccessful()
        ->assertSee('User-agent');
});

it('serves custom robots.txt content when set', function (): void {
    Setting::set('robots_txt', "User-agent: *\nDisallow: /private\n");

    $this->get('/robots.txt')
        ->assertSuccessful()
        ->assertSee('Disallow: /private');
});

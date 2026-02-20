<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('renders the login page', function (): void {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Log In to BlogWriter');
});

it('authenticates user with valid credentials', function (): void {
    $this->post('/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($this->user);
});

it('rejects invalid credentials', function (): void {
    $this->post('/login', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors();

    $this->assertGuest();
});

it('redirects authenticated user away from login page', function (): void {
    $this->actingAs($this->user)
        ->get('/login')
        ->assertRedirect();
});

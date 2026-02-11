<?php

use App\Models\User;

it('redirects to home route after successful login', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
});

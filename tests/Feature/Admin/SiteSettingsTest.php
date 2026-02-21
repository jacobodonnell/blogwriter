<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('redirects unauthenticated users from site settings', function (): void {
    $this->get(route('admin.settings.site'))
        ->assertRedirect(route('login'));
});

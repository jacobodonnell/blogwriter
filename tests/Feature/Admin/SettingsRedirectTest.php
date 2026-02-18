<?php

use App\Models\User;

it('redirects /admin/settings to /admin/settings/profile', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin/settings')
        ->assertRedirect('/admin/settings/profile');
});

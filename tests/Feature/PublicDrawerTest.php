<?php

use App\Models\User;

it('renders drawer without drawer-open class hardcoded for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('drawerOpen && isDesktop', false)
        ->assertDontSee("drawerOpen && 'drawer-open'", false);
});

it('uses x-ref for drawer toggle checkbox instead of x-model', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('x-ref="drawerToggle"', false)
        ->assertDontSee('x-model="drawerOpen"', false);
});

it('has isDesktop property in drawer Alpine component', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('isDesktop: window.matchMedia', false);
});

it('toggles checkbox directly on mobile instead of Alpine state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('$refs.drawerToggle.checked', false);
});

it('does not render drawer layout for guests', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('drawer-toggle', false);
});

it('listens for media query changes to sync desktop state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee("mql.addEventListener('change'", false);
});

<?php

use App\Models\User;

it('renders unified sidebar layout for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('sidebarExpanded', false)
        ->assertSee('mobileDrawerOpen', false);
});

it('has expand/collapse toggle functionality', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('toggle()', false)
        ->assertSee('closeMobile()', false);
});

it('has isDesktop property in sidebar Alpine component', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('isDesktop: window.matchMedia', false);
});

it('persists sidebar state in localStorage', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee("localStorage.setItem('sidebarExpanded'", false)
        ->assertSee("localStorage.getItem('sidebarExpanded')", false);
});

it('does not render sidebar layout for guests', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('sidebarExpanded', false);
});

it('listens for media query changes to sync desktop state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee("mql.addEventListener('change'", false);
});

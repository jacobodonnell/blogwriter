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

it('renders guest mobile drawer with Alpine state', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('mobileMenuOpen: false', false)
        ->assertSee('darkMode:', false);
});

it('renders hamburger button for guest mobile menu', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('ph-list', false)
        ->assertSee('Open menu', false);
});

it('renders guest mobile drawer panel with nav links', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('-translate-x-full', false)
        ->assertSee('ph-house', false)
        ->assertSee('ph-article', false)
        ->assertSee('ph-camera', false)
        ->assertSee('ph-user', false);
});

it('renders guest mobile drawer backdrop', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('bg-black/50', false);
});

it('closes guest drawer on escape key', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('keydown.escape.window', false);
});

it('hides desktop nav links on mobile for guests', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('hidden lg:flex', false);
});

it('listens for media query changes to sync desktop state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee("mql.addEventListener('change'", false);
});

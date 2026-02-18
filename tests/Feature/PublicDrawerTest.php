<?php

use App\Models\User;

describe('authenticated sidebar', function (): void {
    beforeEach(function (): void {
        $this->actingAs(User::factory()->create());
    });

    it('renders the extracted sidebar Alpine component', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('x-data="sidebar"', false)
            ->assertDontSee('mobileMenuOpen: false', false);
    });

    it('does not inline sidebar state', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertDontSee('sidebarExpanded', false)
            ->assertDontSee('isDesktop: window.matchMedia', false);
    });
});

describe('guest layout', function (): void {
    it('does not render sidebar component', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertDontSee('x-data="sidebar"', false);
    });

    it('renders mobile drawer with Alpine state', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('mobileMenuOpen: false', false)
            ->assertSee('themeMode:', false);
    });

    it('renders hamburger button for mobile menu', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('ph-list', false)
            ->assertSee('Open menu', false);
    });

    it('renders mobile drawer panel with nav links', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('-translate-x-full', false)
            ->assertSee('ph-house', false)
            ->assertSee('ph-article', false)
            ->assertSee('ph-camera', false)
            ->assertSee('ph-user', false);
    });

    it('renders mobile drawer backdrop', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('bg-black/50', false);
    });

    it('closes drawer on escape key', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('keydown.escape.window', false);
    });

    it('hides desktop nav links on mobile', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('hidden lg:flex', false);
    });
});

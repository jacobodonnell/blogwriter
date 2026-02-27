<?php

declare(strict_types=1);

use App\Models\User;

describe('authenticated sidebar', function (): void {
    beforeEach(function (): void {
        $this->actingAs(User::factory()->create());
    });

    it('renders the sidebar component for authenticated users', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('x-data="sidebar"', false);
    });
});

describe('guest layout', function (): void {
    it('does not render sidebar component for guests', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertDontSee('x-data="sidebar"', false);
    });

    it('renders mobile menu toggle for guests', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee('Open menu', false);
    });

    it('renders navigation links in mobile drawer', function (): void {
        $this->get(route('home'))
            ->assertSuccessful()
            ->assertSee(route('home'), false)
            ->assertSee(route('articles.index'), false)
            ->assertSee(route('photos.index'), false);
    });

});

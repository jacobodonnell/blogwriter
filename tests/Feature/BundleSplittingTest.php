<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;
use App\View\Components\Layouts\Base;

it('has jsEntry property with app-guest default', function (): void {
    $component = new Base;

    expect($component->jsEntry)->toBe('resources/js/app-guest.js');
});

it('accepts custom jsEntry value', function (): void {
    $component = new Base(jsEntry: 'resources/js/app-admin.js');

    expect($component->jsEntry)->toBe('resources/js/app-admin.js');
});

describe('guest pages', function (): void {
    it('renders guest bundle on homepage', function (): void {
        $this->get('/')
            ->assertSuccessful()
            ->assertSee('app-guest', false)
            ->assertDontSee('app-admin', false);
    });

    it('renders guest bundle on login page', function (): void {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSee('app-guest', false)
            ->assertDontSee('app-admin', false);
    });

    it('renders guest bundle on public article page', function (): void {
        $article = Article::factory()->published()->create();

        $this->get(route('articles.show', $article->slug))
            ->assertSuccessful()
            ->assertSee('app-guest', false)
            ->assertDontSee('app-admin', false);
    });
});

describe('admin pages', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create();
    });

    it('renders admin bundle on dashboard', function (): void {
        $this->actingAs($this->user)
            ->get(route('admin.dashboard'))
            ->assertSuccessful()
            ->assertSee('app-admin', false)
            ->assertDontSee('app-guest', false);
    });

    it('renders admin bundle on article index', function (): void {
        $this->actingAs($this->user)
            ->get(route('admin.articles.index'))
            ->assertSuccessful()
            ->assertSee('app-admin', false)
            ->assertDontSee('app-guest', false);
    });

    it('renders admin bundle on customizer', function (): void {
        $article = Article::factory()->create();

        $this->actingAs($this->user)
            ->get(route('admin.articles.edit', $article))
            ->assertSuccessful()
            ->assertSee('app-admin', false)
            ->assertDontSee('app-guest', false);
    });

    it('renders admin bundle on homepage when authenticated', function (): void {
        $this->actingAs($this->user)
            ->get('/')
            ->assertSuccessful()
            ->assertSee('app-admin', false)
            ->assertDontSee('app-guest', false);
    });
});

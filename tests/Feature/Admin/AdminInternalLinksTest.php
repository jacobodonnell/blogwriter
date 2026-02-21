<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders View Articles link without new tab target', function (): void {
    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful()
        ->assertSee('View Articles', false)
        ->assertSee('ph-eye', false);
});

it('renders View Photos link without new tab target', function (): void {
    $response = $this->get(route('admin.photos.index'));

    $response->assertSuccessful()
        ->assertSee('View Photos', false)
        ->assertSee('ph-eye', false);
});

it('renders View Category link without new tab target', function (): void {
    Category::factory()->create(['name' => 'Technology']);

    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful()
        ->assertSee('View category', false)
        ->assertSee('ph-eye', false);
});

it('uses eye icon instead of external link icon for internal view links', function (): void {
    Category::factory()->create(['name' => 'Tech']);

    $this->get(route('admin.articles.index'))
        ->assertSee('ph-eye', false)
        ->assertDontSee('ph-arrow-square-out', false);

    $this->get(route('admin.photos.index'))
        ->assertSee('ph-eye', false)
        ->assertDontSee('ph-arrow-square-out', false);

    $this->get(route('admin.categories.index'))
        ->assertSee('ph-eye', false)
        ->assertDontSee('ph-arrow-square-out', false);
});

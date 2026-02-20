<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('admin dashboard has breadcrumb bar with only Dashboard', function (): void {
    $content = $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard');
});

it('admin articles index has breadcrumbs', function (): void {
    $content = $this->actingAs($this->user)
        ->get(route('admin.articles.index'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Articles');
});

it('admin categories index has breadcrumbs', function (): void {
    $content = $this->actingAs($this->user)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Categories');
});

it('admin categories edit has breadcrumbs with category name', function (): void {
    $category = Category::factory()->create(['name' => 'Test Category']);

    $content = $this->actingAs($this->user)
        ->get(route('admin.categories.edit', $category))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Categories')
        ->toContain('Edit: Test Category');
});

it('admin photos index has breadcrumbs', function (): void {
    $content = $this->actingAs($this->user)
        ->get(route('admin.photos.index'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Photos');
});

it('admin photos create has breadcrumbs', function (): void {
    $content = $this->actingAs($this->user)
        ->get(route('admin.photos.create'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Photos')
        ->toContain('New Photo');
});

it('admin photos show has breadcrumbs with alt text', function (): void {
    $photo = Photo::factory()->create(['alt_text' => 'Sunset over mountains']);

    $content = $this->actingAs($this->user)
        ->get(route('admin.photos.show', $photo))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Photos')
        ->toContain('Sunset over mountains');
});

it('admin photos edit has breadcrumbs with alt text', function (): void {
    $photo = Photo::factory()->create(['alt_text' => 'Mountain lake']);

    $content = $this->actingAs($this->user)
        ->get(route('admin.photos.edit', $photo))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('breadcrumbs')
        ->toContain('Dashboard')
        ->toContain('Photos')
        ->toContain('Edit: Mountain lake');
});

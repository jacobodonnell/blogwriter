<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filter banner toggles open and closed on categories page', function (): void {
    Article::factory()->published()->create();

    $page = visit('/categories');

    // Click "Filters" to toggle open
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');

    // Click again to collapse
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertMissing('input[name="search"]');
})->group('slow');

it('persists filter state across navigation on categories page', function (): void {
    Article::factory()->published()->create();

    $page = visit('/categories');

    // Open filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');

    // Navigate away and back
    $page->navigate('/categories')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');
})->group('slow');

it('selecting sort updates content via AJAX on categories page', function (): void {
    Article::factory()->published()->create([
        'title' => 'Alpha Article',
        'published_at' => now()->subDays(5),
    ]);
    Article::factory()->published()->create([
        'title' => 'Zulu Article',
        'published_at' => now(),
    ]);

    $page = visit('/categories');

    // Open filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5);

    // Select Title A-Z sort
    $page->select('select[name="sort"]', 'title_asc')
        ->wait(1);

    $page->assertSee('Alpha Article')
        ->assertSee('Zulu Article');
})->group('slow');

it('selecting category filters content via AJAX on categories page', function (): void {
    $category = Category::factory()->create(['name' => 'Nature']);
    Article::factory()->published()->create([
        'title' => 'Nature Walk',
        'category_id' => $category->id,
    ]);
    Article::factory()->published()->create([
        'title' => 'Tech Talk',
    ]);

    $page = visit('/categories');

    // Open filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5);

    // Select category
    $page->select('select[name="category"]', $category->slug)
        ->wait(1);

    $page->assertSee('Nature Walk')
        ->assertDontSee('Tech Talk');
})->group('slow');

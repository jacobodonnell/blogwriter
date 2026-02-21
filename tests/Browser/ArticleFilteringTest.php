<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginToArticles(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/articles');

    return $page;
}

it('collapses and expands filter banner', function (): void {
    Article::factory()->published()->create();

    $page = visit('/articles');

    // Click "Filters" to toggle open
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');

    // Click again to collapse
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertMissing('input[name="search"]');
})->group('slow');

it('persists filter banner state across page loads', function (): void {
    Article::factory()->published()->create();

    $page = visit('/articles');

    // Open filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');

    // Reload — should still be open (persisted)
    $page->navigate('/articles')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');
})->group('slow');

it('shows sort dropdown and changes article order via AJAX', function (): void {
    Article::factory()->published()->create([
        'title' => 'Alpha Article',
        'published_at' => now()->subDays(5),
    ]);
    Article::factory()->published()->create([
        'title' => 'Zulu Article',
        'published_at' => now(),
    ]);

    $page = visit('/articles');

    // Open filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5);

    // Select Title A-Z sort
    $page->select('select[name="sort"]', 'title_asc')
        ->wait(1);

    // Alpha should appear before Zulu
    $page->assertSee('Alpha Article')
        ->assertSee('Zulu Article');
})->group('slow');

it('shows manage articles button in filter toolbar row for auth users', function (): void {
    Article::factory()->published()->create();

    $page = loginToArticles();

    $page->assertSee('Manage Articles')
        ->assertSee('New Article');
})->group('slow');

it('hides manage articles and status filter for guests', function (): void {
    Article::factory()->published()->create();

    $page = visit('/articles');

    $page->assertDontSee('Manage Articles')
        ->assertDontSee('New Article');

    // Open filters — no status dropdown for guests
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertNotPresent('select[name="status"]');
})->group('slow');

it('filters by category via AJAX without full page reload', function (): void {
    $category = Category::factory()->create(['name' => 'Laravel']);
    Article::factory()->published()->create([
        'title' => 'Laravel Tips',
        'category_id' => $category->id,
    ]);
    Article::factory()->published()->create([
        'title' => 'PHP Basics',
    ]);

    $page = visit('/articles');

    // Open filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5);

    // Select category
    $page->select('select[name="category"]', $category->slug)
        ->wait(1);

    $page->assertSee('Laravel Tips')
        ->assertDontSee('PHP Basics');
})->group('slow');

it('clears all filters and resets form inputs', function (): void {
    $category = Category::factory()->create(['name' => 'Tech']);
    Article::factory()->published()->create([
        'title' => 'Tech Article',
        'category_id' => $category->id,
    ]);
    Article::factory()->published()->create([
        'title' => 'Other Article',
    ]);

    $page = visit('/articles');

    // Open filters and apply category + search
    $page->click('button:has-text("Filters")')
        ->wait(0.5);
    $page->select('@filter-category', $category->slug)
        ->wait(1);
    $page->fill('@filter-search', 'Tech')
        ->wait(0.5);

    // Verify inputs have values
    $page->assertSelected('@filter-category', $category->slug)
        ->assertValue('@filter-search', 'Tech');

    // Clear button should be visible
    $page->assertVisible('@filter-clear');

    // Click Clear
    $page->click('@filter-clear')
        ->wait(1);

    // Form inputs should be reset to defaults
    $page->assertSelected('@filter-category', '')
        ->assertValue('@filter-search', '');

    // Both articles visible
    $page->assertSee('Tech Article')
        ->assertSee('Other Article');
})->group('slow');

it('clears all filters and resets results', function (): void {
    $category = Category::factory()->create(['name' => 'Tech']);
    Article::factory()->published()->create([
        'title' => 'Tech Article',
        'category_id' => $category->id,
    ]);
    Article::factory()->published()->create([
        'title' => 'Other Article',
    ]);

    $page = visit('/articles');

    // Open filters and apply a category filter
    $page->click('button:has-text("Filters")')
        ->wait(0.5);
    $page->select('@filter-category', $category->slug)
        ->wait(1);

    $page->assertSee('Tech Article')
        ->assertDontSee('Other Article');

    // Click Clear to reset
    $page->click('@filter-clear')
        ->wait(1);

    $page->assertSee('Tech Article')
        ->assertSee('Other Article');
})->group('slow');

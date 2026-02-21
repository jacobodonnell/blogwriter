<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'test@blogwriter.test',
        'password' => 'password',
    ]);
});

function loginToAdminArticles(): mixed
{
    $page = visit('/login');

    $page->fill('@login-email', 'test@blogwriter.test')
        ->fill('@login-password', 'password')
        ->click('@login-submit')
        ->assertPathIs('/');

    $page->navigate('/admin/articles');

    return $page;
}

it('collapses and expands admin filter card', function (): void {
    Article::factory()->published()->create();

    $page = loginToAdminArticles();

    // Filters should be open by default ($persist default is true)
    $page->assertVisible('input[name="search"]');

    // Click "Filters" to collapse
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertMissing('input[name="search"]');

    // Click again to expand
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertVisible('input[name="search"]');
})->group('slow');

it('persists admin filter state across page loads', function (): void {
    Article::factory()->published()->create();

    $page = loginToAdminArticles();

    // Collapse filters
    $page->click('button:has-text("Filters")')
        ->wait(0.5)
        ->assertMissing('input[name="search"]');

    // Reload — should still be collapsed (persisted)
    $page->navigate('/admin/articles')
        ->wait(0.5)
        ->assertMissing('input[name="search"]');
})->group('slow');

it('filters articles and updates table via AJAX', function (): void {
    $category = Category::factory()->create(['name' => 'DevOps']);
    Article::factory()->published()->create([
        'title' => 'DevOps Guide',
        'category_id' => $category->id,
    ]);
    Article::factory()->published()->create([
        'title' => 'Cooking Tips',
    ]);

    $page = loginToAdminArticles();

    // Filter by category
    $page->select('select[name="category"]', $category->slug)
        ->wait(1);

    $page->assertSee('DevOps Guide')
        ->assertDontSee('Cooking Tips');
})->group('slow');

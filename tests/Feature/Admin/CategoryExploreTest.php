<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

// --- Auth Guard ---

it('redirects guests to login', function (): void {
    $this->get(route('admin.categories.explore'))
        ->assertRedirect(route('login'));
});

// --- Root Explore ---

it('loads the explore index for authenticated users', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.categories.explore'))
        ->assertOk()
        ->assertViewIs('admin.categories.explore')
        ->assertSee('Explore Categories');
});

it('shows root categories as folder navigation', function (): void {
    Category::factory()->create(['name' => 'Alpha']);
    Category::factory()->create(['name' => 'Beta']);

    $this->actingAs($this->user)
        ->get(route('admin.categories.explore'))
        ->assertOk()
        ->assertSee('Alpha')
        ->assertSee('Beta');
});

it('shows article and photo counts', function (): void {
    Article::factory()->published()->count(3)->create();
    Photo::factory()->published()->count(2)->create();

    $this->actingAs($this->user)
        ->get(route('admin.categories.explore'))
        ->assertOk()
        ->assertSee('3 articles')
        ->assertSee('2 photos');
});

// --- Drill-Down ---

it('drills into a subcategory', function (): void {
    $parent = Category::factory()->create(['name' => 'Technology']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'Laravel']);
    Article::factory()->published()->create(['category_id' => $child->id]);

    $this->actingAs($this->user)
        ->get(route('admin.categories.explore.show', $parent->slug.'/'.$child->slug))
        ->assertOk()
        ->assertSee('Laravel');
});

it('returns 404 for invalid category path', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.categories.explore.show', 'non-existent'))
        ->assertNotFound();
});

// --- Filters ---

it('filters by content type', function (): void {
    $category = Category::factory()->create();
    Article::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Filtered Article',
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.categories.explore').'?type=articles')
        ->assertOk()
        ->assertSee('Filtered Article');
});

it('filters by search', function (): void {
    Article::factory()->published()->create(['title' => 'Unique Searchable Title']);
    Article::factory()->published()->create(['title' => 'Another Article']);

    $this->actingAs($this->user)
        ->get(route('admin.categories.explore').'?search=Unique+Searchable')
        ->assertOk()
        ->assertSee('Unique Searchable Title');
});

// --- AJAX Partials ---

it('returns partial view for AJAX requests', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.categories.explore'), ['X-Alpine-Target' => 'explore-content'])
        ->assertOk()
        ->assertViewIs('admin.categories._explore-content');
});

it('returns partial view for drill-down AJAX requests', function (): void {
    $category = Category::factory()->create();

    $this->actingAs($this->user)
        ->get(
            route('admin.categories.explore.show', $category->slug),
            ['X-Alpine-Target' => 'explore-content']
        )
        ->assertOk()
        ->assertViewIs('admin.categories._explore-content');
});

// --- Category Model Methods ---

it('generates urlFor articles', function (): void {
    $category = Category::factory()->create(['slug' => 'tech']);

    expect($category->urlFor('articles'))->toContain('/articles')
        ->and($category->urlFor('articles'))->toContain('category=tech');
});

it('generates urlFor photos', function (): void {
    $category = Category::factory()->create(['slug' => 'nature']);

    expect($category->urlFor('photos'))->toContain('/photos')
        ->and($category->urlFor('photos'))->toContain('category=nature');
});

it('generates adminExploreUrl', function (): void {
    $parent = Category::factory()->create(['slug' => 'tech']);
    $child = Category::factory()->withParent($parent)->create(['slug' => 'laravel']);

    expect($child->adminExploreUrl())->toContain('/admin/categories/explore/tech/laravel');
});

it('generates adminExploreUrl for root category', function (): void {
    $category = Category::factory()->create(['slug' => 'tech']);

    expect($category->adminExploreUrl())->toContain('/admin/categories/explore/tech');
});

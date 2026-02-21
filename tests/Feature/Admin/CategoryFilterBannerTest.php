<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('filters categories by parent_id', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);
    Category::factory()->withParent($root)->create(['name' => 'PHP']);
    Category::factory()->create(['name' => 'Photography']);

    $response = $this->get(route('admin.categories.index', ['parent_id' => $root->id]));

    $response->assertOk()
        ->assertViewHas('categories', fn ($cats) => $cats->pluck('name')->contains('PHP'))
        ->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('Photography'))
        ->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('Programming'));
});

it('filters categories by name search', function (): void {
    Category::factory()->create(['name' => 'Programming']);
    Category::factory()->create(['name' => 'Photography']);

    $response = $this->get(route('admin.categories.index', ['search' => 'Program']));

    $response->assertOk()
        ->assertViewHas('categories', fn ($cats) => $cats->pluck('name')->contains('Programming'))
        ->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('Photography'));
});

it('filters categories by slug search', function (): void {
    Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-dev']);
    Category::factory()->create(['name' => 'Photography', 'slug' => 'photography']);

    $response = $this->get(route('admin.categories.index', ['search' => 'web-dev']));

    $response->assertOk()
        ->assertViewHas('categories', fn ($cats) => $cats->pluck('name')->contains('Web Development'))
        ->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('Photography'));
});

it('filters categories by content type articles', function (): void {
    $withArticles = Category::factory()->create(['name' => 'Has Articles']);
    Category::factory()->create(['name' => 'No Articles']);
    Article::factory()->create(['category_id' => $withArticles->id]);

    $response = $this->get(route('admin.categories.index', ['content_type' => 'articles']));

    $response->assertOk()
        ->assertViewHas('categories', fn ($cats) => $cats->pluck('name')->contains('Has Articles'))
        ->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('No Articles'));
});

it('filters categories by content type photos', function (): void {
    $withPhotos = Category::factory()->create(['name' => 'Has Photos']);
    Category::factory()->create(['name' => 'No Photos']);
    Photo::factory()->create(['category_id' => $withPhotos->id]);

    $response = $this->get(route('admin.categories.index', ['content_type' => 'photos']));

    $response->assertOk()
        ->assertViewHas('categories', fn ($cats) => $cats->pluck('name')->contains('Has Photos'))
        ->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('No Photos'));
});

it('paginates categories with default per page', function (): void {
    Category::factory()->count(25)->create();

    $response = $this->get(route('admin.categories.index'));

    $response->assertOk()
        ->assertViewHas('categories', fn ($categories) => $categories->count() === 20);
});

it('respects per page parameter', function (): void {
    Category::factory()->count(15)->create();

    $response = $this->get(route('admin.categories.index', ['perPage' => 10]));

    $response->assertOk()
        ->assertViewHas('categories', fn ($categories) => $categories->count() === 10);
});

it('returns table partial for ajax requests', function (): void {
    $response = $this->get(
        route('admin.categories.index', ['search' => 'test']),
        ['X-Alpine-Target' => 'categories-table']
    );

    $response->assertOk()
        ->assertSee('id="categories-table"', false)
        ->assertDontSee('<h1', false);
});

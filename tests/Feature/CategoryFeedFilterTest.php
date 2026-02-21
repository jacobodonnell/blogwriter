<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;

it('sorts articles by oldest first on categories index', function (): void {
    $old = Article::factory()->published()->create([
        'title' => 'Old Article',
        'published_at' => now()->subDays(10),
    ]);
    $new = Article::factory()->published()->create([
        'title' => 'New Article',
        'published_at' => now(),
    ]);

    $response = $this->get(route('categories.index', ['sort' => 'oldest', 'type' => 'articles']));

    $response->assertOk();
    $articles = $response->viewData('articles');
    expect($articles->first()->title)->toBe('Old Article')
        ->and($articles->last()->title)->toBe('New Article');
});

it('sorts articles by title A-Z on categories index', function (): void {
    Article::factory()->published()->create(['title' => 'Zebra']);
    Article::factory()->published()->create(['title' => 'Alpha']);

    $articles = $this->get(route('categories.index', ['sort' => 'title_asc', 'type' => 'articles']))
        ->assertOk()
        ->viewData('articles');

    expect($articles->first()->title)->toBe('Alpha')
        ->and($articles->last()->title)->toBe('Zebra');
});

it('sorts articles by title Z-A on categories index', function (): void {
    Article::factory()->published()->create(['title' => 'Alpha']);
    Article::factory()->published()->create(['title' => 'Zebra']);

    $articles = $this->get(route('categories.index', ['sort' => 'title_desc', 'type' => 'articles']))
        ->assertOk()
        ->viewData('articles');

    expect($articles->first()->title)->toBe('Zebra')
        ->and($articles->last()->title)->toBe('Alpha');
});

it('sorts photos by oldest first on categories index', function (): void {
    Photo::factory()->published()->create([
        'alt_text' => 'Old Photo',
        'published_at' => now()->subDays(10),
    ]);
    Photo::factory()->published()->create([
        'alt_text' => 'New Photo',
        'published_at' => now(),
    ]);

    $photos = $this->get(route('categories.index', ['sort' => 'oldest', 'type' => 'photos']))
        ->assertOk()
        ->viewData('photos');

    expect($photos->first()->alt_text)->toBe('Old Photo')
        ->and($photos->last()->alt_text)->toBe('New Photo');
});

it('sorts photos by title A-Z on categories index', function (): void {
    Photo::factory()->published()->create(['alt_text' => 'Zebra Photo']);
    Photo::factory()->published()->create(['alt_text' => 'Alpha Photo']);

    $photos = $this->get(route('categories.index', ['sort' => 'title_asc', 'type' => 'photos']))
        ->assertOk()
        ->viewData('photos');

    expect($photos->first()->alt_text)->toBe('Alpha Photo')
        ->and($photos->last()->alt_text)->toBe('Zebra Photo');
});

it('filters by category slug on categories index', function (): void {
    $category = Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Article::factory()->published()->create(['title' => 'Laravel Post', 'category_id' => $category->id]);
    Article::factory()->published()->create(['title' => 'Other Post', 'category_id' => null]);

    $articles = $this->get(route('categories.index', ['category' => 'laravel', 'type' => 'articles']))
        ->assertOk()
        ->viewData('articles');

    expect($articles)->toHaveCount(1)
        ->and($articles->first()->title)->toBe('Laravel Post');
});

it('filters photos by category slug on categories index', function (): void {
    $category = Category::factory()->create(['name' => 'Nature', 'slug' => 'nature']);
    Photo::factory()->published()->create(['alt_text' => 'Nature Shot', 'category_id' => $category->id]);
    Photo::factory()->published()->create(['alt_text' => 'Random Shot', 'category_id' => null]);

    $photos = $this->get(route('categories.index', ['category' => 'nature', 'type' => 'photos']))
        ->assertOk()
        ->viewData('photos');

    expect($photos)->toHaveCount(1)
        ->and($photos->first()->alt_text)->toBe('Nature Shot');
});

it('combines search sort and category filters on categories index', function (): void {
    $category = Category::factory()->create(['slug' => 'tech']);
    Article::factory()->published()->create([
        'title' => 'Alpha Tech',
        'category_id' => $category->id,
        'published_at' => now()->subDay(),
    ]);
    Article::factory()->published()->create([
        'title' => 'Zebra Tech',
        'category_id' => $category->id,
        'published_at' => now(),
    ]);
    Article::factory()->published()->create([
        'title' => 'Alpha Other',
        'category_id' => null,
    ]);

    $articles = $this->get(route('categories.index', [
        'category' => 'tech',
        'sort' => 'title_asc',
        'type' => 'articles',
    ]))->assertOk()->viewData('articles');

    expect($articles)->toHaveCount(2)
        ->and($articles->first()->title)->toBe('Alpha Tech')
        ->and($articles->last()->title)->toBe('Zebra Tech');
});

it('sort persists across content type tab switches', function (): void {
    Article::factory()->published()->create(['title' => 'Alpha', 'published_at' => now()->subDay()]);
    Article::factory()->published()->create(['title' => 'Zebra', 'published_at' => now()]);

    // Sort=title_asc with type=articles
    $articles = $this->get(route('categories.index', ['sort' => 'title_asc', 'type' => 'articles']))
        ->assertOk()
        ->viewData('articles');

    expect($articles->first()->title)->toBe('Alpha');

    // Same sort with type=all
    $articles = $this->get(route('categories.index', ['sort' => 'title_asc']))
        ->assertOk()
        ->viewData('articles');

    expect($articles->first()->title)->toBe('Alpha');
});

it('category dropdown is present in categories filter form', function (): void {
    Category::factory()->create(['name' => 'Test Cat']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('name="category"', false);
});

it('sort dropdown is present in categories filter form', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('name="sort"', false);
});

it('returns correct content for ajax partial request', function (): void {
    Article::factory()->published()->create(['title' => 'AJAX Article']);

    $this->get(route('categories.index'), ['X-Alpine-Target' => 'category-content'])
        ->assertOk()
        ->assertSee('AJAX Article');
});

it('sorts articles on single category page', function (): void {
    $category = Category::factory()->create();
    Article::factory()->published()->create([
        'title' => 'Zebra',
        'category_id' => $category->id,
        'published_at' => now(),
    ]);
    Article::factory()->published()->create([
        'title' => 'Alpha',
        'category_id' => $category->id,
        'published_at' => now()->subDay(),
    ]);

    $articles = $this->get(route('categories.show', $category->slug).'?sort=title_asc&type=articles')
        ->assertOk()
        ->viewData('articles');

    expect($articles->first()->title)->toBe('Alpha')
        ->and($articles->last()->title)->toBe('Zebra');
});

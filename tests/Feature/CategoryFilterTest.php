<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

it('shows all content by default on categories index', function (): void {
    $category = Category::factory()->create();
    Article::factory()->published()->create(['title' => 'Test Article', 'category_id' => $category->id]);
    Photo::factory()->published()->create(['alt_text' => 'Test Photo', 'category_id' => $category->id]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertViewHas('currentType', 'all')
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('shows only articles when type is articles', function (): void {
    $category = Category::factory()->create();
    Article::factory()->published()->create(['category_id' => $category->id]);
    Photo::factory()->published()->create(['category_id' => $category->id]);

    $this->get(route('categories.index', ['type' => 'articles']))
        ->assertOk()
        ->assertViewHas('currentType', 'articles')
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos->isEmpty());
});

it('shows only photos when type is photos', function (): void {
    $category = Category::factory()->create();
    Article::factory()->published()->create(['category_id' => $category->id]);
    Photo::factory()->published()->create(['category_id' => $category->id]);

    $this->get(route('categories.index', ['type' => 'photos']))
        ->assertOk()
        ->assertViewHas('currentType', 'photos')
        ->assertViewHas('articles', fn ($articles) => $articles->isEmpty())
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('falls back to all for invalid type', function (): void {
    $this->get(route('categories.index', ['type' => 'invalid']))
        ->assertOk()
        ->assertViewHas('currentType', 'all');
});

it('filters articles by search on title', function (): void {
    Article::factory()->published()->create(['title' => 'Laravel Tips']);
    Article::factory()->published()->create(['title' => 'Photography Guide']);

    $this->get(route('categories.index', ['search' => 'Laravel', 'type' => 'articles']))
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1 && $articles->first()->title === 'Laravel Tips');
});

it('filters photos by search on alt_text and caption', function (): void {
    Photo::factory()->published()->create(['alt_text' => 'Sunset over ocean', 'caption' => 'Beautiful']);
    Photo::factory()->published()->create(['alt_text' => 'Mountain peak', 'caption' => 'Snowy']);

    $this->get(route('categories.index', ['search' => 'sunset', 'type' => 'photos']))
        ->assertOk()
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);

    $this->get(route('categories.index', ['search' => 'Snowy', 'type' => 'photos']))
        ->assertOk()
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('auth user can filter by draft status', function (): void {
    Article::factory()->draft()->create(['title' => 'Draft Article']);
    Article::factory()->published()->create(['title' => 'Published Article']);

    $this->actingAs(User::first())
        ->get(route('categories.index', ['status' => 'draft', 'type' => 'articles']))
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1 && $articles->first()->title === 'Draft Article');
});

it('guest ignores status filter', function (): void {
    Article::factory()->published()->create(['title' => 'Published Only']);
    Article::factory()->draft()->create(['title' => 'Draft Only']);

    $this->get(route('categories.index', ['status' => 'draft', 'type' => 'articles']))
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1 && $articles->first()->title === 'Published Only');
});

it('includes uncategorized articles', function (): void {
    Article::factory()->published()->create(['title' => 'Uncategorized Post', 'category_id' => null]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1);
});

it('includes uncategorized photos', function (): void {
    Photo::factory()->published()->create(['alt_text' => 'No Category Photo', 'category_id' => null]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('shows root categories as children', function (): void {
    Category::factory()->create(['name' => 'Root Cat']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertViewHas('children', fn ($children) => $children->count() === 1 && $children->first()->name === 'Root Cat');
});

it('shows no content found when filters match nothing', function (): void {
    Article::factory()->published()->create(['title' => 'Existing']);

    $this->get(route('categories.index', ['search' => 'nonexistent']))
        ->assertOk()
        ->assertSee('No content found')
        ->assertSee('Try adjusting your filters');
});

it('shows no content yet when no content exists and no filters', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('No content yet');
});

it('shows filter banner', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Filters');
});

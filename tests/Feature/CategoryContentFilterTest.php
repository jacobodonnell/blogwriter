<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->category = Category::factory()->create(['name' => 'Filter Test']);
    $this->article = Article::factory()->published()->create([
        'title' => 'Test Article',
        'category_id' => $this->category->id,
    ]);
    $this->photo = Photo::factory()->published()->create([
        'alt_text' => 'Test Photo Alt',
        'caption' => 'A beautiful sunset',
        'category_id' => $this->category->id,
    ]);
});

it('defaults to all content when no type param', function (): void {
    $this->get(route('categories.show', $this->category->slug))
        ->assertOk()
        ->assertViewHas('currentType', 'all')
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('shows only articles when type is articles', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=articles')
        ->assertOk()
        ->assertViewHas('currentType', 'articles')
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos->isEmpty());
});

it('shows only photos when type is photos', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=photos')
        ->assertOk()
        ->assertViewHas('currentType', 'photos')
        ->assertViewHas('articles', fn ($articles) => $articles->isEmpty())
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('falls back to all for invalid type', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=invalid')
        ->assertOk()
        ->assertViewHas('currentType', 'all')
        ->assertViewHas('articles', fn ($articles) => $articles !== null)
        ->assertViewHas('photos', fn ($photos) => $photos !== null);
});

it('respects draft visibility with type filter', function (): void {
    $draftArticle = Article::factory()->draft()->create([
        'title' => 'Draft Article',
        'category_id' => $this->category->id,
    ]);

    // Guests should not see draft articles
    $this->get(route('categories.show', $this->category->slug).'?type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1);

    // Auth users should see draft articles
    $this->actingAs(User::first())
        ->get(route('categories.show', $this->category->slug).'?type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 2);
});

it('pagination works with type filter', function (): void {
    Article::factory()->published()->count(12)->create(['category_id' => $this->category->id]);

    $this->get(route('categories.show', $this->category->slug).'?type=articles&articles_page=2')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->currentPage() === 2);
});

it('filters articles by search on title', function (): void {
    Article::factory()->published()->create([
        'title' => 'Laravel Tips',
        'category_id' => $this->category->id,
    ]);

    $this->get(route('categories.show', $this->category->slug).'?search=Laravel&type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1 && $articles->first()->title === 'Laravel Tips');
});

it('filters photos by search on alt_text', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?search=Photo+Alt&type=photos')
        ->assertOk()
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('filters photos by search on caption', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?search=sunset&type=photos')
        ->assertOk()
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 1);
});

it('search returns no results when nothing matches', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?search=nonexistent')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->isEmpty())
        ->assertViewHas('photos', fn ($photos) => $photos->isEmpty());
});

it('auth user can filter by published status', function (): void {
    Article::factory()->draft()->create([
        'title' => 'Draft Only',
        'category_id' => $this->category->id,
    ]);

    $this->actingAs(User::first())
        ->get(route('categories.show', $this->category->slug).'?status=published&type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1 && $articles->first()->title === 'Test Article');
});

it('auth user can filter by draft status', function (): void {
    Article::factory()->draft()->create([
        'title' => 'Draft Only',
        'category_id' => $this->category->id,
    ]);

    $this->actingAs(User::first())
        ->get(route('categories.show', $this->category->slug).'?status=draft&type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1 && $articles->first()->title === 'Draft Only');
});

it('guest ignores status filter', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?status=draft&type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1);
});

it('search and type filters work together', function (): void {
    Article::factory()->published()->create([
        'title' => 'Unique Searchable',
        'category_id' => $this->category->id,
    ]);

    $this->get(route('categories.show', $this->category->slug).'?search=Unique&type=articles')
        ->assertOk()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos->isEmpty());
});

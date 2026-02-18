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
        'category_id' => $this->category->id,
    ]);
});

it('defaults to all content when no type param', function (): void {
    $this->get(route('categories.show', $this->category->slug))
        ->assertOk()
        ->assertViewHas('currentType', 'all')
        ->assertViewHas('articles', fn ($articles) => $articles !== null && $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos !== null && $photos->count() === 1);
});

it('shows only articles when type is articles', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=articles')
        ->assertOk()
        ->assertViewHas('currentType', 'articles')
        ->assertViewHas('articles', fn ($articles) => $articles !== null && $articles->count() === 1)
        ->assertViewHas('photos', fn ($photos) => $photos === null);
});

it('shows only photos when type is photos', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=photos')
        ->assertOk()
        ->assertViewHas('currentType', 'photos')
        ->assertViewHas('articles', fn ($articles) => $articles === null)
        ->assertViewHas('photos', fn ($photos) => $photos !== null && $photos->count() === 1);
});

it('falls back to all for invalid type', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=invalid')
        ->assertOk()
        ->assertViewHas('currentType', 'all')
        ->assertViewHas('articles', fn ($articles) => $articles !== null)
        ->assertViewHas('photos', fn ($photos) => $photos !== null);
});

it('returns partial for AJAX requests', function (): void {
    $this->get(route('categories.show', $this->category->slug).'?type=articles', [
        'X-Alpine-Target' => 'category-content',
    ])
        ->assertOk()
        ->assertViewIs('public.category._content');
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
    $user = User::first();
    $this->actingAs($user)
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

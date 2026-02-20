<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->category = Category::factory()->create(['name' => 'Empty Category']);
    $this->user = User::factory()->create();
});

it('shows create buttons for auth user on empty category page', function (): void {
    $this->actingAs($this->user)
        ->get(route('categories.show', $this->category->slug))
        ->assertOk()
        ->assertSee('Write Article')
        ->assertSee('Upload Photo');
});

it('hides create buttons for guests on empty category page', function (): void {
    $this->get(route('categories.show', $this->category->slug))
        ->assertOk()
        ->assertDontSee('Write Article')
        ->assertDontSee('Upload Photo');
});

it('shows only article button when type filter is articles', function (): void {
    $this->actingAs($this->user)
        ->get(route('categories.show', $this->category->slug).'?type=articles')
        ->assertOk()
        ->assertSee('Write Article')
        ->assertDontSee('Upload Photo');
});

it('shows only photo button when type filter is photos', function (): void {
    $this->actingAs($this->user)
        ->get(route('categories.show', $this->category->slug).'?type=photos')
        ->assertOk()
        ->assertDontSee('Write Article')
        ->assertSee('Upload Photo');
});

it('create links include category_id param', function (): void {
    $articleCreateUrl = route('admin.articles.create', ['category_id' => $this->category->id]);
    $photoCreateUrl = route('admin.photos.create', ['category_id' => $this->category->id]);

    $this->actingAs($this->user)
        ->get(route('categories.show', $this->category->slug))
        ->assertOk()
        ->assertSee($articleCreateUrl, false)
        ->assertSee($photoCreateUrl, false);
});

it('preselects category on article create page via category_id', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.create', ['category_id' => $this->category->id]))
        ->assertOk()
        ->assertViewHas('article', fn ($article) => (int) $article->category_id === $this->category->id);
});

it('preselects category on photo create page via category_id', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.photos.create', ['category_id' => $this->category->id]))
        ->assertOk()
        ->assertViewHas('photo', fn ($photo) => (int) $photo->category_id === $this->category->id);
});

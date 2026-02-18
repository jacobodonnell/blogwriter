<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

// --- Articles Index ---

it('shows draft articles to authenticated users on articles index', function (): void {
    $user = User::factory()->create();
    $draft = Article::factory()->draft()->create();

    $this->actingAs($user)
        ->get('/articles')
        ->assertSuccessful()
        ->assertSee($draft->title);
});

it('hides draft articles from guests on articles index', function (): void {
    $draft = Article::factory()->draft()->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertDontSee($draft->title);
});

// --- Article Show ---

it('allows authenticated users to view draft articles', function (): void {
    $user = User::factory()->create();
    $draft = Article::factory()->draft()->create(['slug' => 'draft-article']);

    $this->actingAs($user)
        ->get('/articles/draft-article')
        ->assertSuccessful()
        ->assertSee($draft->title);
});

it('returns 404 for guests viewing draft articles', function (): void {
    Article::factory()->draft()->create(['slug' => 'draft-article']);

    $this->get('/articles/draft-article')
        ->assertNotFound();
});

it('shows draft badge on article show page for authenticated users', function (): void {
    $user = User::factory()->create();
    $draft = Article::factory()->draft()->create(['slug' => 'draft-article']);

    $this->actingAs($user)
        ->get('/articles/draft-article')
        ->assertSuccessful()
        ->assertSee('Draft');
});

// --- Photos Index ---

it('shows draft photos to authenticated users on photos index', function (): void {
    $user = User::factory()->create();
    $draft = Photo::factory()->draft()->create(['alt_text' => 'Draft Photo Alt']);

    $this->actingAs($user)
        ->get('/photos')
        ->assertSuccessful()
        ->assertSee('Draft Photo Alt');
});

it('hides draft photos from guests on photos index', function (): void {
    $draft = Photo::factory()->draft()->create(['alt_text' => 'Draft Photo Alt']);

    $this->get('/photos')
        ->assertSuccessful()
        ->assertDontSee('Draft Photo Alt');
});

// --- Photo Show ---

it('allows authenticated users to view draft photos', function (): void {
    $user = User::factory()->create();
    $draft = Photo::factory()->draft()->create(['slug' => 'draft-photo']);

    $this->actingAs($user)
        ->get('/photos/draft-photo')
        ->assertSuccessful();
});

it('returns 404 for guests viewing draft photos', function (): void {
    Photo::factory()->draft()->create(['slug' => 'draft-photo']);

    $this->get('/photos/draft-photo')
        ->assertNotFound();
});

// --- Category Page ---

it('shows draft articles to authenticated users on category page', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $draft = Article::factory()->draft()->create(['category_id' => $category->id]);

    $this->actingAs($user)
        ->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertSee($draft->title);
});

it('hides draft articles from guests on category page', function (): void {
    $category = Category::factory()->create();
    $draft = Article::factory()->draft()->create(['category_id' => $category->id]);

    $this->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertDontSee($draft->title);
});

it('shows photos on category page', function (): void {
    $category = Category::factory()->create();
    $photo = Photo::factory()->published()->create([
        'category_id' => $category->id,
        'alt_text' => 'Category Photo',
    ]);

    $this->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertSee('Category Photo');
});

it('shows draft photos to authenticated users on category page', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $draftPhoto = Photo::factory()->draft()->create([
        'category_id' => $category->id,
        'alt_text' => 'Draft Category Photo',
    ]);

    $this->actingAs($user)
        ->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertSee('Draft Category Photo');
});

it('hides draft photos from guests on category page', function (): void {
    $category = Category::factory()->create();
    Photo::factory()->draft()->create([
        'category_id' => $category->id,
        'alt_text' => 'Draft Category Photo',
    ]);

    $this->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertDontSee('Draft Category Photo');
});

it('displays photo count in category header', function (): void {
    $category = Category::factory()->create();
    Photo::factory()->published()->count(3)->create(['category_id' => $category->id]);

    $this->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertSee('3 photos');
});

it('passes photos to category view', function (): void {
    $category = Category::factory()->create();
    Photo::factory()->published()->count(2)->create(['category_id' => $category->id]);

    $this->get(route('categories.show', $category->slug))
        ->assertSuccessful()
        ->assertViewHas('photos', fn ($photos) => $photos->count() === 2);
});

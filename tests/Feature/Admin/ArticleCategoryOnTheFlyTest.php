<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
    $this->article = Article::factory()->draft()->for($this->user)->create();
});

it('creates a category on the fly and assigns it to the article', function (): void {
    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'Web Development',
        'new_category_slug' => 'web-development',
    ])->assertRedirect(route('admin.articles.edit', $this->article));

    $category = Category::where('name', 'Web Development')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('web-development')
        ->and($this->article->fresh()->category_id)->toBe($category->id);
});

it('stores parent_id and description when provided', function (): void {
    $parent = Category::factory()->create();

    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'Child Category',
        'new_category_parent_id' => $parent->id,
        'new_category_description' => 'A child of the parent.',
    ])->assertRedirect();

    $category = Category::where('name', 'Child Category')->first();

    expect($category)->not->toBeNull()
        ->and($category->parent_id)->toBe($parent->id)
        ->and($category->description)->toBe('A child of the parent.');
});

it('returns validation error for non-existent parent_id', function (): void {
    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'Orphan Category',
        'new_category_parent_id' => 99999,
    ])->assertSessionHasErrors('new_category_parent_id');
});

it('auto-generates slug when new_category_slug is omitted', function (): void {
    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'My Cool Category',
    ])->assertRedirect();

    $category = Category::where('name', 'My Cool Category')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->not->toBeEmpty()
        ->and($this->article->fresh()->category_id)->toBe($category->id);
});

it('returns validation error for duplicate category slug', function (): void {
    Category::factory()->create(['slug' => 'existing-slug']);

    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'New Category',
        'new_category_slug' => 'existing-slug',
    ])->assertSessionHasErrors('new_category_slug');
});

it('returns validation error when new_category_name is too short', function (): void {
    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'A',
    ])->assertSessionHasErrors('new_category_name');
});

it('normal save without new_category_name is unaffected', function (): void {
    $categoryCount = Category::count();

    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
    ])->assertRedirect(route('admin.articles.edit', $this->article));

    expect(Category::count())->toBe($categoryCount)
        ->and($this->article->fresh()->category_id)->toBeNull();
});

it('new_category_name takes precedence over category_id when both are submitted', function (): void {
    $existing = Category::factory()->create(['name' => 'Existing Category']);

    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'category_id' => $existing->id,
        'new_category_name' => 'Brand New Category',
    ])->assertRedirect();

    $newCategory = Category::where('name', 'Brand New Category')->first();

    expect($newCategory)->not->toBeNull()
        ->and($this->article->fresh()->category_id)->toBe($newCategory->id);
});

it('store path creates category on the fly and assigns it to the new article', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'New Article',
        'slug' => 'new-article',
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'Fresh Category',
        'new_category_slug' => 'fresh-category',
    ])->assertRedirect();

    $category = Category::where('name', 'Fresh Category')->first();
    $article = Article::where('slug', 'new-article')->first();

    expect($category)->not->toBeNull()
        ->and($article)->not->toBeNull()
        ->and($article->category_id)->toBe($category->id);
});

it('requires authentication to save with new category', function (): void {
    auth()->logout();

    put(route('admin.articles.update', $this->article), [
        'title' => $this->article->title,
        'slug' => $this->article->slug,
        'content' => 'Some content here',
        'status' => Status::Draft->value,
        'new_category_name' => 'Unauthorized Category',
    ])->assertRedirect();

    expect(Category::where('name', 'Unauthorized Category')->exists())->toBeFalse();
});

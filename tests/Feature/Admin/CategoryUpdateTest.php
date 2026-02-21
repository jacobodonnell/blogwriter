<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('updates category name and slug', function (): void {
    $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    put(route('admin.categories.update', $category), [
        'name' => 'New Name',
        'slug' => 'new-name',
    ])->assertRedirect(route('admin.categories.index'));

    $category->refresh();

    expect($category->name)->toBe('New Name')
        ->and($category->slug)->toBe('new-name');
});

it('updates category parent', function (): void {
    $parent = Category::factory()->create();
    $category = Category::factory()->create(['parent_id' => null]);

    put(route('admin.categories.update', $category), [
        'name' => $category->name,
        'parent_id' => $parent->id,
    ])->assertRedirect();

    expect($category->fresh()->parent_id)->toBe($parent->id);
});

it('removes category parent', function (): void {
    $parent = Category::factory()->create();
    $category = Category::factory()->create(['parent_id' => $parent->id]);

    put(route('admin.categories.update', $category), [
        'name' => $category->name,
        'parent_id' => null,
    ])->assertRedirect();

    expect($category->fresh()->parent_id)->toBeNull();
});

it('validates name is required', function (): void {
    $category = Category::factory()->create();

    put(route('admin.categories.update', $category), [
        'name' => '',
    ])->assertSessionHasErrors('name');
});

it('validates slug uniqueness excluding self', function (): void {
    $other = Category::factory()->create(['slug' => 'taken-slug']);
    $category = Category::factory()->create(['slug' => 'my-slug']);

    put(route('admin.categories.update', $category), [
        'name' => $category->name,
        'slug' => 'taken-slug',
    ])->assertSessionHasErrors('slug');
});

it('allows keeping the same slug on update', function (): void {
    $category = Category::factory()->create(['slug' => 'my-slug']);

    put(route('admin.categories.update', $category), [
        'name' => 'Updated Name',
        'slug' => 'my-slug',
    ])->assertRedirect();

    expect($category->fresh()->name)->toBe('Updated Name');
});

it('requires authentication', function (): void {
    auth()->logout();
    $category = Category::factory()->create();

    put(route('admin.categories.update', $category), [
        'name' => 'Updated',
    ])->assertRedirect();
});

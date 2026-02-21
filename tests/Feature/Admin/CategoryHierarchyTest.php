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

it('shows all categories including subcategories in flat table', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);

    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful();
    $response->assertSee('Programming');
    $response->assertSee('PHP');
});

it('creates category with parent_id', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming']);

    $this->post(route('admin.categories.store'), [
        'name' => 'PHP',
        'slug' => 'php',
        'parent_id' => $parent->id,
    ])->assertRedirect(route('admin.categories.index'));

    $child = Category::where('slug', 'php')->first();

    expect($child)->not->toBeNull()
        ->and($child->parent_id)->toBe($parent->id);
});

it('prevents deletion when category has children', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming']);
    Category::factory()->withParent($parent)->create(['name' => 'PHP']);

    $this->delete(route('admin.categories.destroy', $parent))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Category::find($parent->id))->not->toBeNull();
});

it('prevents deletion when category has photos', function (): void {
    $category = Category::factory()->create();
    Photo::factory()->create(['category_id' => $category->id]);

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Category::find($category->id))->not->toBeNull();
});

it('prevents deletion when category has articles', function (): void {
    $category = Category::factory()->create(['name' => 'Tech']);
    Article::factory()->create(['category_id' => $category->id]);

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Category::find($category->id))->not->toBeNull();
});

it('allows deletion when category has no children or articles', function (): void {
    $category = Category::factory()->create(['name' => 'Empty']);

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success');

    expect(Category::find($category->id))->toBeNull();
});

it('excludes self and descendants from parent dropdown on edit', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);

    $response = $this->get(route('admin.categories.edit', $root));

    $response->assertSuccessful();
    $response->assertDontSee('value="'.$root->id.'"', false);
    $response->assertDontSee('value="'.$child->id.'"', false);
});

it('auto-generates slug when slug is not provided', function (): void {
    $this->post(route('admin.categories.store'), [
        'name' => 'My New Category',
        'slug' => '',
    ])->assertRedirect();

    $category = Category::where('name', 'My New Category')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('my-new-category');
});

it('validates name is required', function (): void {
    $response = $this->post(route('admin.categories.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});

it('store returns partial response for ajax request', function (): void {
    $response = $this->post(
        route('admin.categories.store'),
        ['name' => 'Technology', 'slug' => 'technology'],
        ['X-Alpine-Target' => 'add-category-form']
    );

    $response->assertOk();
    $response->assertSee('category:created');
    $response->assertSee('id="add-category-form"', false);
    expect(Category::where('name', 'Technology')->exists())->toBeTrue();
});

it('rejects duplicate slug via ajax with validation errors', function (): void {
    Category::factory()->create(['name' => 'Tech', 'slug' => 'tech']);

    $response = $this->post(
        route('admin.categories.store'),
        ['name' => 'Technology', 'slug' => 'tech'],
        ['X-Alpine-Target' => 'add-category-form']
    );

    $response->assertUnprocessable();
    $response->assertSee('id="add-category-form"', false);
    expect(Category::where('name', 'Technology')->exists())->toBeFalse();
});

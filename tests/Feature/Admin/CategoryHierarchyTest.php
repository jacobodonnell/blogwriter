<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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

it('returns table partial for AJAX requests on index', function (): void {
    Category::factory()->create(['name' => 'Programming']);

    $response = $this->get(
        route('admin.categories.index'),
        ['X-Alpine-Target' => 'categories-table']
    );

    $response->assertSuccessful();
    $response->assertSee('id="categories-table"', false);
    $response->assertDontSee('<h1', false);
});

it('filters categories by parent_id', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);
    $other = Category::factory()->create(['name' => 'Photography']);

    $response = $this->get(route('admin.categories.index', ['parent_id' => $root->id]));

    $response->assertSuccessful();
    $response->assertViewHas('categories', fn ($cats) => $cats->pluck('name')->contains('PHP'));
    $response->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('Photography'));
    $response->assertViewHas('categories', fn ($cats) => ! $cats->pluck('name')->contains('Programming'));
});

it('creates category with parent_id', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming']);

    $this->post(route('admin.categories.store'), [
        'name' => 'PHP',
        'slug' => 'php',
        'parent_id' => $parent->id,
    ])->assertRedirect();

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

it('redirects to index after creating child category', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

    $response = $this->post(route('admin.categories.store'), [
        'name' => 'PHP',
        'slug' => 'php',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect(route('admin.categories.index'));
});

it('add category modal is present on index page', function (): void {
    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful();
    $response->assertSee('add-category-modal', false);
    $response->assertSee('Add Category');
});

it('modal shows validation errors on empty name', function (): void {
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
    $response->assertSee('action="'.route('admin.categories.store').'"', false);
    expect(Category::where('name', 'Technology')->exists())->toBeTrue();
});

it('store success partial includes parent context for subcategory', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

    $response = $this->post(
        route('admin.categories.store'),
        ['name' => 'PHP', 'slug' => 'php', 'parent_id' => $parent->id],
        ['X-Alpine-Target' => 'add-category-form']
    );

    $response->assertOk();
    $response->assertSee('Add Subcategory');
});

it('store redirects for non-ajax request', function (): void {
    $response = $this->post(route('admin.categories.store'), [
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

it('shows validation errors in form when duplicate slug submitted via ajax', function (): void {
    Category::factory()->create(['name' => 'Tech', 'slug' => 'tech']);

    $response = $this->post(
        route('admin.categories.store'),
        ['name' => 'Technology', 'slug' => 'tech'],
        ['X-Alpine-Target' => 'add-category-form']
    );

    $response->assertStatus(422);
    $response->assertSee('id="add-category-form"', false);
    $response->assertSee('slug', false);
});

it('does not create category when slug is duplicate', function (): void {
    Category::factory()->create(['name' => 'Existing Tech', 'slug' => 'tech']);

    $response = $this->post(
        route('admin.categories.store'),
        ['name' => 'Technology', 'slug' => 'tech'],
        ['X-Alpine-Target' => 'add-category-form']
    );

    $response->assertUnprocessable();
    expect(Category::where('name', 'Technology')->exists())->toBeFalse();
});

it('add form shows parent category dropdown on index page', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);

    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful();
    $response->assertSee('Parent Category');
    $response->assertSee('None (Root)');
    $response->assertSee('Programming');
});

it('add form shows all categories in parent dropdown including leaf categories', function (): void {
    $root = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
    $leaf = Category::factory()->withParent($root)->create(['name' => 'PHP', 'slug' => 'php']);

    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful();
    $response->assertSee('value="'.$root->id.'"', false);
    $response->assertSee('value="'.$leaf->id.'"', false);
});

it('creates subcategory under leaf category selected from parent dropdown', function (): void {
    $leaf = Category::factory()->create(['name' => 'Design', 'slug' => 'design']);

    $this->post(route('admin.categories.store'), [
        'name' => 'UI Design',
        'slug' => 'ui-design',
        'parent_id' => $leaf->id,
    ])->assertRedirect();

    $child = Category::where('slug', 'ui-design')->first();

    expect($child)->not->toBeNull()
        ->and($child->parent_id)->toBe($leaf->id);
});

it('categories.children route no longer exists', function (): void {
    expect(Route::has('admin.categories.children'))->toBeFalse();
});

<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('shows root categories by default', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);

    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful();
    $response->assertSee('Programming');
    $response->assertDontSee('>PHP<', false);
});

it('drills down to show children when parent is specified', function (): void {
    $root = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);

    $response = $this->get(route('admin.categories.children', $root->slug));

    $response->assertSuccessful();
    $response->assertSee('PHP');
});

it('returns table partial for AJAX requests', function (): void {
    $root = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

    $response = $this->get(
        route('admin.categories.children', $root->slug),
        ['X-Alpine-Target' => 'categories-table']
    );

    $response->assertSuccessful();
    $response->assertSee('id="categories-table"', false);
    $response->assertDontSee('<h1', false);
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
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Category::find($category->id))->toBeNull();
});

it('builds correct breadcrumbs for nested category', function (): void {
    $root = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP', 'slug' => 'php']);
    $grandchild = Category::factory()->withParent($child)->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $response = $this->get(route('admin.categories.children', 'programming/php/laravel'));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Root', 'Programming', 'PHP', 'Laravel']);
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

it('redirects to parent context after creating child category', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

    $response = $this->post(route('admin.categories.store'), [
        'name' => 'PHP',
        'slug' => 'php',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect(route('admin.categories.children', $parent->slug));
});

it('resolves nested slug path to correct category', function (): void {
    $root = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP', 'slug' => 'php']);

    $response = $this->get(route('admin.categories.children', 'programming/php'));

    $response->assertSuccessful();
    $response->assertSee('PHP');
    $response->assertSee('Programming');
});

it('returns 404 for invalid slug path', function (): void {
    Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

    $this->get(route('admin.categories.children', 'programming/nonexistent'))
        ->assertNotFound();
});

it('store redirects to slug path URL after creating child', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

    $response = $this->post(route('admin.categories.store'), [
        'name' => 'JavaScript',
        'slug' => 'javascript',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect(route('admin.categories.children', 'programming'));
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

it('category drill-down links are standard navigation without ajax target', function (): void {
    $root = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
    Category::factory()->withParent($root)->create(['name' => 'PHP']);

    $response = $this->get(route('admin.categories.children', 'programming'));

    $response->assertSuccessful();
    $response->assertDontSee('x-target.push', false);
});

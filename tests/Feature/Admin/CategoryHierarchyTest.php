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
    $root = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);

    $response = $this->get(route('admin.categories.index', ['parent' => $root->id]));

    $response->assertSuccessful();
    $response->assertSee('PHP');
});

it('returns table partial for AJAX requests', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);

    $response = $this->get(
        route('admin.categories.index', ['parent' => $root->id]),
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

it('includes descendant articles in article count', function (): void {
    $parent = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'PHP']);

    Article::factory()->published()->create(['category_id' => $parent->id]);
    Article::factory()->published()->create(['category_id' => $child->id]);

    expect($parent->article_count)->toBe(2);
});

it('builds correct breadcrumbs for nested category', function (): void {
    $root = Category::factory()->create(['name' => 'Programming']);
    $child = Category::factory()->withParent($root)->create(['name' => 'PHP']);
    $grandchild = Category::factory()->withParent($child)->create(['name' => 'Laravel']);

    $response = $this->get(route('admin.categories.index', ['parent' => $grandchild->id]));

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
    $parent = Category::factory()->create(['name' => 'Programming']);

    $response = $this->post(route('admin.categories.store'), [
        'name' => 'PHP',
        'slug' => 'php',
        'parent_id' => $parent->id,
    ]);

    $response->assertRedirect(route('admin.categories.index').'?parent='.$parent->id);
});

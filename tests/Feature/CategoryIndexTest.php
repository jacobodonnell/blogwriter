<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

it('renders the categories index page', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertViewIs('public.categories');
});

it('shows all root categories', function (): void {
    $cat1 = Category::factory()->create(['name' => 'Alpha Category']);
    $cat2 = Category::factory()->create(['name' => 'Beta Category']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Alpha Category')
        ->assertSee('Beta Category');
});

it('does not show child categories at root level', function (): void {
    $parent = Category::factory()->create(['name' => 'Parent Category']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'Child Category']);

    $response = $this->get(route('categories.index'));

    $response->assertOk()
        ->assertSee('Parent Category')
        ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->doesntContain($child->id));
});

it('shows article, photo, and subcategory counts', function (): void {
    $category = Category::factory()->create(['name' => 'Counted Category']);
    Article::factory()->published()->count(3)->create(['category_id' => $category->id]);
    Photo::factory()->published()->count(2)->create(['category_id' => $category->id]);
    Category::factory()->withParent($category)->count(1)->create();

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('3 articles')
        ->assertSee('2 photos')
        ->assertSee('1 subcategory');
});

it('links to category permalink', function (): void {
    $category = Category::factory()->create(['name' => 'Linked Category']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee($category->permalink());
});

it('shows empty state when no categories exist', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('No categories yet');
});

it('shows manage link for auth users only', function (): void {
    Category::factory()->create();

    $guestResponse = $this->get(route('categories.index'));
    $guestResponse->assertOk()
        ->assertDontSee('Manage');

    $user = User::factory()->create();
    $authResponse = $this->actingAs($user)->get(route('categories.index'));
    $authResponse->assertOk()
        ->assertSee('Manage');
});

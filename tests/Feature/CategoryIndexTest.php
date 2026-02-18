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

it('shows root categories as collapsible children', function (): void {
    Category::factory()->create(['name' => 'Alpha Category']);
    Category::factory()->create(['name' => 'Beta Category']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Alpha Category')
        ->assertSee('Beta Category');
});

it('does not show child categories in collapsible nav', function (): void {
    $parent = Category::factory()->create(['name' => 'Parent Category']);
    Category::factory()->withParent($parent)->create(['name' => 'Child Category']);

    $response = $this->get(route('categories.index'));

    $response->assertOk()
        ->assertSee('Parent Category')
        ->assertViewHas('children', fn ($children) => $children->pluck('name')->doesntContain('Child Category'));
});

it('shows total article and photo counts in header', function (): void {
    $category = Category::factory()->create();
    Article::factory()->published()->count(3)->create(['category_id' => $category->id]);
    Photo::factory()->published()->count(2)->create(['category_id' => $category->id]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('3 articles')
        ->assertSee('2 photos');
});

it('links to category permalink in children nav', function (): void {
    $category = Category::factory()->create(['name' => 'Linked Category']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee($category->permalink());
});

it('shows no content yet when no content exists', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('No content yet');
});

it('hides draft content from guest counts', function (): void {
    Article::factory()->published()->count(2)->create();
    Article::factory()->draft()->create();
    Photo::factory()->published()->create();
    Photo::factory()->draft()->create();

    // Guest sees only published counts
    $this->get(route('categories.index'))
        ->assertSee('2 articles')
        ->assertSee('1 photo');

    // Auth user sees all counts
    $this->actingAs(User::first())
        ->get(route('categories.index'))
        ->assertSee('3 articles')
        ->assertSee('2 photos');
});

it('shows manage link for auth users only', function (): void {
    $guestResponse = $this->get(route('categories.index'));
    $guestResponse->assertOk()
        ->assertDontSee('Manage');

    $user = User::factory()->create();
    $authResponse = $this->actingAs($user)->get(route('categories.index'));
    $authResponse->assertOk()
        ->assertSee('Manage');
});

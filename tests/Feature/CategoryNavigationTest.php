<?php

use App\Models\Category;

it('categories listing contains morph target with correct id and attributes', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('id="category-content"', false)
        ->assertSee('x-merge="morph"', false)
        ->assertSee('data-page-title=', false);
});

it('category feed contains morph target with correct id and attributes', function (): void {
    $category = Category::factory()->create();

    $this->get(route('categories.show', $category->slug))
        ->assertOk()
        ->assertSee('id="category-content"', false)
        ->assertSee('x-merge="morph"', false)
        ->assertSee('data-page-title=', false);
});

it('categories listing has collapsible children with x-target.push', function (): void {
    Category::factory()->create(['name' => 'Root Cat']);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Root Cat')
        ->assertSee('x-target.push="category-content"', false);
});

it('category feed has collapsible subcategory chips with x-target.push', function (): void {
    $parent = Category::factory()->create(['name' => 'Parent']);
    Category::factory()->withParent($parent)->create(['name' => 'Child Sub']);

    $this->get(route('categories.show', $parent->slug))
        ->assertOk()
        ->assertSee('Child Sub')
        ->assertSee('x-target.push="category-content"', false);
});

it('both routes have content-type tabs with x-target.push', function (): void {
    $category = Category::factory()->create();

    // Categories index
    $indexResponse = $this->get(route('categories.index'));
    $indexResponse->assertOk();
    $indexContent = $indexResponse->getContent();
    expect($indexContent)->toContain('aria-label="Content filter"');

    // Category show
    $showResponse = $this->get(route('categories.show', $category->slug));
    $showResponse->assertOk();
    $showContent = $showResponse->getContent();
    expect($showContent)->toContain('aria-label="Content filter"');
});

it('both routes have breadcrumbs', function (): void {
    $category = Category::factory()->create();

    // Categories index has breadcrumbs
    $indexResponse = $this->get(route('categories.index'));
    $indexResponse->assertOk();
    expect($indexResponse->getContent())->toContain('breadcrumbs');

    // Category show has breadcrumbs
    $showResponse = $this->get(route('categories.show', $category->slug));
    $showResponse->assertOk();
    expect($showResponse->getContent())->toContain('breadcrumbs');
});

it('category breadcrumbs include ancestors', function (): void {
    $parent = Category::factory()->create(['name' => 'Parent Cat']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'Child Cat']);

    $response = $this->get(route('categories.show', $parent->slug.'/'.$child->slug));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('Categories');
    expect($content)->toContain('Parent Cat');
    expect($content)->toContain('Child Cat');
});

it('both routes have filter section', function (): void {
    $category = Category::factory()->create();

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Filters');

    $this->get(route('categories.show', $category->slug))
        ->assertOk()
        ->assertSee('Filters');
});

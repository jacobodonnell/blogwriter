<?php

use App\Models\Category;

it('tree scope returns only root categories', function (): void {
    $root = Category::factory()->create(['name' => 'Root']);
    $child = Category::factory()->withParent($root)->create(['name' => 'Child']);

    $tree = Category::tree()->get();

    expect($tree)->toHaveCount(1)
        ->and($tree->first()->name)->toBe('Root');
});

it('tree scope eager loads children', function (): void {
    $root = Category::factory()->create(['name' => 'Root']);
    Category::factory()->withParent($root)->create(['name' => 'Child']);

    $tree = Category::tree()->get();

    expect($tree->first()->relationLoaded('children'))->toBeTrue()
        ->and($tree->first()->children)->toHaveCount(1);
});

it('tree scope orders by name', function (): void {
    Category::factory()->create(['name' => 'Zebra']);
    Category::factory()->create(['name' => 'Alpha']);
    Category::factory()->create(['name' => 'Middle']);

    $tree = Category::tree()->get();

    expect($tree->pluck('name')->all())->toBe(['Alpha', 'Middle', 'Zebra']);
});

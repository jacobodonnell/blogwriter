<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('cannot delete category with photos', function (): void {
    $category = Category::factory()->create();
    Photo::factory()->create(['category_id' => $category->id]);

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

it('can delete category with zero articles photos and children', function (): void {
    $category = Category::factory()->create();

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('delete button hidden when category has photos', function (): void {
    $category = Category::factory()->create();
    Photo::factory()->create(['category_id' => $category->id]);

    $this->get(route('admin.categories.index'))
        ->assertOk()
        ->assertDontSee('action="'.route('admin.categories.destroy', $category).'"', false);
});

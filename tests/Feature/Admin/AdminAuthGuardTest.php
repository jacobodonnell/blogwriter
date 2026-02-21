<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
    Storage::fake('private');
});

it('redirects unauthenticated users on admin GET routes', function (string $routeName, array $params): void {
    $this->get(route($routeName, $params))
        ->assertRedirect();
})->with([
    'articles index' => ['admin.articles.index', []],
    'photos index' => ['admin.photos.index', []],
    'photos create' => ['admin.photos.create', []],
    'categories index' => ['admin.categories.index', []],
    'categories explore' => ['admin.categories.explore', []],
]);

it('redirects unauthenticated users on admin article routes', function (): void {
    $article = Article::factory()->draft()->create();

    $this->get(route('admin.articles.edit', $article))->assertRedirect();
    $this->put(route('admin.articles.update', $article), [
        'title' => 'Hacked',
        'slug' => 'hacked',
        'content' => 'x',
        'status' => 'draft',
    ])->assertRedirect();
    $this->delete(route('admin.articles.destroy', $article))->assertRedirect();

    expect($article->fresh()->title)->not->toBe('Hacked');
});

it('redirects unauthenticated users on admin photo routes', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->get(route('admin.photos.edit', $photo))->assertRedirect();
    $this->put(route('admin.photos.update', $photo), [
        'alt_text' => 'Hacked',
        'status' => 'draft',
    ])->assertRedirect();
    $this->delete(route('admin.photos.destroy', $photo))->assertRedirect();

    expect($photo->fresh()->alt_text)->not->toBe('Hacked');
});

it('redirects unauthenticated users on admin category routes', function (): void {
    $category = Category::factory()->create();

    $this->get(route('admin.categories.edit', $category))->assertRedirect();
    $this->put(route('admin.categories.update', $category), [
        'name' => 'Hacked',
    ])->assertRedirect();
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect();

    expect($category->fresh()->name)->not->toBe('Hacked');
});

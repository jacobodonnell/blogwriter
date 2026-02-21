<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('deletes an article and redirects to index', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    delete(route('admin.articles.destroy', $article))
        ->assertRedirect(route('admin.articles.index'))
        ->assertSessionHas('success');

    expect(Article::find($article->id))->toBeNull();
});

it('does not delete the featured photo when article is deleted', function (): void {
    $photo = Photo::factory()->published()->create();
    $article = Article::factory()->draft()->for($this->user)->create([
        'photo_id' => $photo->id,
    ]);

    delete(route('admin.articles.destroy', $article))
        ->assertRedirect();

    expect(Photo::find($photo->id))->not->toBeNull();
});

it('requires authentication to delete an article', function (): void {
    auth()->logout();

    $article = Article::factory()->draft()->create();

    delete(route('admin.articles.destroy', $article))
        ->assertRedirect();

    expect(Article::find($article->id))->not->toBeNull();
});

it('deletes a published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    delete(route('admin.articles.destroy', $article))
        ->assertRedirect(route('admin.articles.index'));

    expect(Article::find($article->id))->toBeNull();
});

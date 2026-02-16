<?php

use App\Actions\GenerateUniqueSlugAction;
use App\Models\Article;

beforeEach(function (): void {
    $this->action = new GenerateUniqueSlugAction;
});

it('returns slug unchanged when unique', function (): void {
    $slug = $this->action->handle('my-article', Article::class);

    expect($slug)->toBe('my-article');
});

it('converts input via Str::slug', function (): void {
    $slug = $this->action->handle('My Article Title!', Article::class);

    expect($slug)->toBe('my-article-title');
});

it('appends -1 when slug already exists', function (): void {
    Article::factory()->create(['slug' => 'my-article']);

    $slug = $this->action->handle('my-article', Article::class);

    expect($slug)->toBe('my-article-1');
});

it('increments suffix for multiple collisions', function (): void {
    Article::factory()->create(['slug' => 'my-article']);
    Article::factory()->create(['slug' => 'my-article-1']);

    $slug = $this->action->handle('my-article', Article::class);

    expect($slug)->toBe('my-article-2');
});

it('ignores specified model ID for updates', function (): void {
    $article = Article::factory()->create(['slug' => 'my-article']);

    $slug = $this->action->handle('my-article', Article::class, $article->id);

    expect($slug)->toBe('my-article');
});

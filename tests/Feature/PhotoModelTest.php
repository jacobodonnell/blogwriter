<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

it('isPublic returns true for published photo with past date', function (): void {
    $photo = Photo::factory()->published()->create([
        'published_at' => now()->subDay(),
    ]);

    expect($photo->isPublic())->toBeTrue();
});

it('isPublic returns false for draft photo', function (): void {
    $photo = Photo::factory()->draft()->create();

    expect($photo->isPublic())->toBeFalse();
});

it('isPublic returns false for published photo with future date', function (): void {
    $photo = Photo::factory()->create([
        'status' => Status::Published,
        'published_at' => now()->addDay(),
    ]);

    expect($photo->isPublic())->toBeFalse();
});

it('has user relationship', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);

    expect($photo->user->id)->toBe($user->id);
});

it('articles relationship returns correct articles', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->published()->create(['user_id' => $user->id]);
    $article1 = Article::factory()->published()->create(['user_id' => $user->id, 'photo_id' => $photo->id]);
    $article2 = Article::factory()->published()->create(['user_id' => $user->id, 'photo_id' => $photo->id]);
    $otherArticle = Article::factory()->published()->create(['user_id' => $user->id]);

    $articles = $photo->articles()->get();

    expect($articles->count())->toBe(2);
    expect($articles->pluck('id'))->toContain($article1->id);
    expect($articles->pluck('id'))->toContain($article2->id);
    expect($articles->pluck('id'))->not->toContain($otherArticle->id);
});

<?php

declare(strict_types=1);

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

it('addPastSlug adds a slug to past_slugs', function (): void {
    $photo = Photo::factory()->create(['past_slugs' => []]);

    $photo->addPastSlug('old-slug');

    expect($photo->past_slugs)->toContain('old-slug');
});

it('addPastSlug does not add duplicate slugs', function (): void {
    $photo = Photo::factory()->create(['past_slugs' => ['existing-slug']]);

    $photo->addPastSlug('existing-slug');

    expect($photo->past_slugs)->toBe(['existing-slug']);
});

it('booted hook tracks old slug when slug changes', function (): void {
    $photo = Photo::factory()->create(['slug' => 'original-slug', 'past_slugs' => []]);

    $photo->slug = 'new-slug';
    $photo->save();

    $photo->refresh();
    expect($photo->past_slugs)->toContain('original-slug');
    expect($photo->slug)->toBe('new-slug');
});

it('booted hook does not add to past_slugs when slug is unchanged', function (): void {
    $photo = Photo::factory()->create(['slug' => 'same-slug', 'past_slugs' => []]);

    $photo->alt_text = 'Updated alt text';
    $photo->save();

    $photo->refresh();
    expect($photo->past_slugs)->toBeEmpty();
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

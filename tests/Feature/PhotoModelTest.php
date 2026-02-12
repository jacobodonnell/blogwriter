<?php

use App\Models\Article;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    expect($photo->isPublic())->toBeFalse();
});

it('isExternalUrl detects external URL photos', function (): void {
    $externalPhoto = Photo::factory()->create([
        'meta' => ['external_url' => 'https://example.com/image.jpg'],
    ]);
    $uploadedPhoto = Photo::factory()->create([
        'meta' => [],
    ]);

    expect($externalPhoto->isExternalUrl())->toBeTrue();
    expect($uploadedPhoto->isExternalUrl())->toBeFalse();
});

it('articles relationship returns correct articles', function (): void {
    $photo = Photo::factory()->published()->create();
    $article1 = Article::factory()->published()->create(['photo_id' => $photo->id]);
    $article2 = Article::factory()->published()->create(['photo_id' => $photo->id]);
    $otherArticle = Article::factory()->published()->create();

    $articles = $photo->articles()->get();

    expect($articles->count())->toBe(2);
    expect($articles->pluck('id'))->toContain($article1->id);
    expect($articles->pluck('id'))->toContain($article2->id);
    expect($articles->pluck('id'))->not->toContain($otherArticle->id);
});

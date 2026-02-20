<?php

use App\Actions\UpdatePublishedStatusAction;
use App\Enums\Status;
use App\Models\Article;

beforeEach(function (): void {
    $this->action = new UpdatePublishedStatusAction;
});

it('sets published_at when publishing for the first time', function (): void {
    $article = new Article;
    $article->published_at = null;

    $this->action->handle($article, Status::Published);

    expect($article->published_at)->not->toBeNull();
});

it('keeps existing published_at when already published', function (): void {
    $article = new Article;
    $existingDate = now()->subDays(5);
    $article->published_at = $existingDate;

    $this->action->handle($article, Status::Published);

    expect($article->published_at->toDateTimeString())
        ->toBe($existingDate->toDateTimeString());
});

it('clears published_at when unpublishing', function (): void {
    $article = new Article;
    $article->published_at = now();

    $this->action->handle($article, Status::Draft);

    expect($article->published_at)->toBeNull();
});

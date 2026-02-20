<?php

declare(strict_types=1);

use App\Models\Article;

it('isPlaceholderSlug returns true for placeholder slugs', function (): void {
    expect(Article::isPlaceholderSlug('untitled-a1b2c3d4'))->toBeTrue();
    expect(Article::isPlaceholderSlug('untitled-00000000'))->toBeTrue();
    expect(Article::isPlaceholderSlug('untitled-abcd1234'))->toBeTrue();
});

it('isPlaceholderSlug returns false for normal slugs', function (): void {
    expect(Article::isPlaceholderSlug('my-article'))->toBeFalse();
    expect(Article::isPlaceholderSlug('untitled'))->toBeFalse();
    expect(Article::isPlaceholderSlug('untitled-toolong123'))->toBeFalse();
    expect(Article::isPlaceholderSlug('untitled-A1B2C3D4'))->toBeFalse();
    expect(Article::isPlaceholderSlug('untitled-a1b2c3'))->toBeFalse();
});

it('wasEdited returns true when last_edited_at is set', function (): void {
    $article = new Article;
    $article->setRawAttributes(['last_edited_at' => '2025-01-01 00:00:00']);

    expect($article->wasEdited())->toBeTrue();
});

it('wasEdited returns false when last_edited_at is null', function (): void {
    $article = new Article;
    $article->setRawAttributes(['last_edited_at' => null]);

    expect($article->wasEdited())->toBeFalse();
});

it('isPublished returns true for published article with past published_at', function (): void {
    $article = new Article;
    $article->setRawAttributes([
        'status' => 'published',
        'published_at' => now()->subDay()->toDateTimeString(),
    ]);

    expect($article->isPublished())->toBeTrue();
});

it('isPublished returns false for draft article', function (): void {
    $article = new Article;
    $article->setRawAttributes([
        'status' => 'draft',
        'published_at' => now()->subDay()->toDateTimeString(),
    ]);

    expect($article->isPublished())->toBeFalse();
});

it('isPublished returns false when published_at is in the future', function (): void {
    $article = new Article;
    $article->setRawAttributes([
        'status' => 'published',
        'published_at' => now()->addDay()->toDateTimeString(),
    ]);

    expect($article->isPublished())->toBeFalse();
});

it('addPastSlug adds slug to past_slugs array', function (): void {
    $article = new Article;
    $article->setRawAttributes(['past_slugs' => json_encode([])]);

    $article->addPastSlug('old-slug');

    expect($article->past_slugs)->toContain('old-slug');
});

it('addPastSlug deduplicates slugs', function (): void {
    $article = new Article;
    $article->setRawAttributes(['past_slugs' => json_encode([])]);

    $article->addPastSlug('old-slug');
    $article->addPastSlug('old-slug');

    expect($article->past_slugs)->toHaveCount(1);
});

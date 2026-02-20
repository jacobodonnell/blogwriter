<?php

declare(strict_types=1);

use App\Models\Article;

it('strips markdown from excerpt when summary is null', function (): void {
    $article = new Article;
    $article->setRawAttributes([
        'summary' => null,
        'content' => 'This has **bold** and [a link](https://example.com)',
    ]);

    $excerpt = $article->excerpt;

    expect($excerpt)->toContain('This has bold and a link')
        ->not->toContain('**')
        ->not->toContain('[')
        ->not->toContain('https://example.com');
});

it('returns summary as excerpt when summary exists', function (): void {
    $article = new Article;
    $article->setRawAttributes([
        'summary' => 'My custom summary',
        'content' => 'Some **markdown** content',
    ]);

    expect($article->excerpt)->toBe('My custom summary');
});

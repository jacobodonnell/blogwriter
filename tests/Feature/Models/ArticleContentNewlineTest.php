<?php

declare(strict_types=1);

use App\Models\Article;

it('expands single newlines to double newlines on save', function (): void {
    $article = Article::factory()->create(['content' => "Line one\nLine two\nLine three"]);

    expect($article->getRawOriginal('content'))->toBe("Line one\n\nLine two\n\nLine three");
});

it('does not double existing double newlines', function (): void {
    $article = Article::factory()->create(['content' => "Line one\n\nLine two"]);

    expect($article->getRawOriginal('content'))->toBe("Line one\n\nLine two");
});

it('preserves fenced code blocks untouched', function (): void {
    $content = "Text before\n```\ncode\nline\n```\nText after";
    $article = Article::factory()->create(['content' => $content]);

    $raw = $article->getRawOriginal('content');

    expect($raw)->toContain("```\ncode\nline\n```");
});

it('collapses double newlines to single on read', function (): void {
    $article = Article::factory()->create(['content' => "Line one\nLine two"]);

    expect($article->content)->toBe("Line one\nLine two");
});

it('preserves triple newlines as intentional blank lines', function (): void {
    $article = Article::factory()->create(['content' => "Para one\n\n\nPara two"]);

    $raw = $article->getRawOriginal('content');
    // Triple+ newlines in input should remain as intentional spacing (at least double after processing)
    expect($raw)->toContain("\n\n");

    // On read, the intentional blank line should be preserved as double newline
    expect($article->content)->toContain("\n\n");
});

it('returns null for null content via accessor', function (): void {
    $article = Article::factory()->make(['content' => null]);

    expect($article->content)->toBeNull();
});

it('renders correct paragraphs from stored double newlines', function (): void {
    $article = Article::factory()->create(['content' => "First paragraph\nSecond paragraph"]);

    $html = $article->content_html;

    expect($html)->toContain('<p>First paragraph</p>');
    expect($html)->toContain('<p>Second paragraph</p>');
});

it('round-trips content correctly', function (): void {
    $original = "Line one\nLine two\nLine three";
    $article = Article::factory()->create(['content' => $original]);

    expect($article->content)->toBe($original);
});

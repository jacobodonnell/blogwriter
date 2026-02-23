<?php

declare(strict_types=1);

use App\Models\Article;

it('stores content as-is (no newline transformation)', function (): void {
    $article = Article::factory()->create(['content' => "Line one\n\nLine two\n\nLine three"]);

    expect($article->content)->toBe("Line one\n\nLine two\n\nLine three");
});

it('round-trips CommonMark content unchanged', function (): void {
    $content = "First paragraph\n\nSecond paragraph";
    $article = Article::factory()->create(['content' => $content]);

    expect($article->content)->toBe($content);
});

it('preserves fenced code blocks untouched', function (): void {
    $content = "Text before\n\n```\ncode\nline\n```\n\nText after";
    $article = Article::factory()->create(['content' => $content]);

    expect($article->content)->toBe($content);
    expect($article->content)->toContain("```\ncode\nline\n```");
});

it('returns null for null content', function (): void {
    $article = Article::factory()->make(['content' => null]);

    expect($article->content)->toBeNull();
});

it('renders correct paragraphs from stored double newlines', function (): void {
    $article = Article::factory()->create(['content' => "First paragraph\n\nSecond paragraph"]);

    $html = $article->content_html;

    expect($html)->toContain('<p>First paragraph</p>');
    expect($html)->toContain('<p>Second paragraph</p>');
});

it('stores single newlines as-is without expansion', function (): void {
    $article = Article::factory()->create(['content' => "Line one\nLine two"]);

    expect($article->content)->toBe("Line one\nLine two");
});

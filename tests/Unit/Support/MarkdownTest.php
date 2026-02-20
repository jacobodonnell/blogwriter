<?php

declare(strict_types=1);

use App\Support\Markdown;

it('strips bold and italic', function (): void {
    $result = Markdown::toPlainText('This is **bold** and *italic* text');

    expect($result)->toBe('This is bold and italic text');
});

it('strips headings', function (): void {
    $result = Markdown::toPlainText("## Heading Two\n\nSome paragraph text");

    expect($result)->toContain('Heading Two')
        ->toContain('Some paragraph text')
        ->not->toContain('#');
});

it('strips links', function (): void {
    $result = Markdown::toPlainText('Check [this link](https://example.com) out');

    expect($result)->toContain('this link')
        ->not->toContain('https://example.com')
        ->not->toContain('[')
        ->not->toContain(']');
});

it('strips blockquotes', function (): void {
    $result = Markdown::toPlainText("> This is a quote\n\nNormal text");

    expect($result)->toContain('This is a quote')
        ->toContain('Normal text')
        ->not->toContain('>');
});

it('strips inline code', function (): void {
    $result = Markdown::toPlainText('Use the `strip_tags()` function');

    expect($result)->toContain('strip_tags()')
        ->not->toContain('`');
});

it('strips HTML tags', function (): void {
    $result = Markdown::toPlainText('<h1>Title</h1><p>Body <strong>text</strong></p>');

    expect($result)->toContain('Title')
        ->toContain('Body')
        ->toContain('text')
        ->not->toContain('<');
});

it('decodes HTML entities', function (): void {
    $result = Markdown::toPlainText('She said "hello" & waved');

    expect($result)->toBe('She said "hello" & waved')
        ->not->toContain('&quot;')
        ->not->toContain('&amp;');
});

it('separates heading text from paragraph text', function (): void {
    $result = Markdown::toPlainText("## Heading\n\nParagraph text here");

    expect($result)->toBe('Heading Paragraph text here');
});

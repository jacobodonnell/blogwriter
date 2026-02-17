<?php

use App\Actions\GenerateArticleSummaryAction;

beforeEach(function (): void {
    $this->action = new GenerateArticleSummaryAction;
});

it('returns provided summary when not blank', function (): void {
    $result = $this->action->handle('My custom summary', '<p>Some content</p>');

    expect($result)->toBe('My custom summary');
});

it('generates summary from content when summary is null', function (): void {
    $result = $this->action->handle(null, 'Hello world');

    expect($result)->toBe('Hello world');
});

it('generates summary from content when summary is empty string', function (): void {
    $result = $this->action->handle('', 'Hello world');

    expect($result)->toBe('Hello world');
});

it('strips HTML tags from content', function (): void {
    $result = $this->action->handle(null, '<h1>Title</h1><p>Body <strong>text</strong></p>');

    expect($result)->toContain('Title')
        ->toContain('Body')
        ->toContain('text')
        ->not->toContain('<');
});

it('strips markdown bold and italic', function (): void {
    $result = $this->action->handle(null, 'This is **bold** and *italic* text');

    expect($result)->toBe('This is bold and italic text');
});

it('strips markdown headings', function (): void {
    $result = $this->action->handle(null, "## Heading Two\n\nSome paragraph text");

    expect($result)->toContain('Heading Two')
        ->toContain('Some paragraph text')
        ->not->toContain('#');
});

it('strips markdown links', function (): void {
    $result = $this->action->handle(null, 'Check [this link](https://example.com) out');

    expect($result)->toContain('this link')
        ->not->toContain('https://example.com')
        ->not->toContain('[')
        ->not->toContain(']');
});

it('strips markdown blockquotes', function (): void {
    $result = $this->action->handle(null, "> This is a quote\n\nNormal text");

    expect($result)->toContain('This is a quote')
        ->toContain('Normal text')
        ->not->toContain('>');
});

it('strips markdown inline code', function (): void {
    $result = $this->action->handle(null, 'Use the `strip_tags()` function');

    expect($result)->toContain('strip_tags()')
        ->not->toContain('`');
});

it('limits generated summary to 255 characters', function (): void {
    $longContent = str_repeat('a', 500);

    $result = $this->action->handle(null, $longContent);

    expect(strlen($result))->toBeLessThanOrEqual(258); // 255 + '...'
});

it('decodes HTML entities from markdown content', function (): void {
    $result = $this->action->handle(null, 'She said "hello" & waved');

    expect($result)->toBe('She said "hello" & waved')
        ->not->toContain('&quot;')
        ->not->toContain('&amp;');
});

it('separates heading text from paragraph text', function (): void {
    $result = $this->action->handle(null, "## Heading\n\nParagraph text here");

    expect($result)->toBe('Heading Paragraph text here');
});

it('handles null content gracefully', function (): void {
    $result = $this->action->handle(null, null);

    expect($result)->toBe('');
});

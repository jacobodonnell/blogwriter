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
    $result = $this->action->handle(null, '<p>Hello world</p>');

    expect($result)->toBe('Hello world');
});

it('generates summary from content when summary is empty string', function (): void {
    $result = $this->action->handle('', '<p>Hello world</p>');

    expect($result)->toBe('Hello world');
});

it('strips HTML tags from content', function (): void {
    $result = $this->action->handle(null, '<h1>Title</h1><p>Body <strong>text</strong></p>');

    expect($result)->toBe('TitleBody text');
});

it('limits generated summary to 255 characters', function (): void {
    $longContent = str_repeat('a', 500);

    $result = $this->action->handle(null, $longContent);

    expect(strlen($result))->toBeLessThanOrEqual(258); // 255 + '...'
});

it('handles null content gracefully', function (): void {
    $result = $this->action->handle(null, null);

    expect($result)->toBe('');
});

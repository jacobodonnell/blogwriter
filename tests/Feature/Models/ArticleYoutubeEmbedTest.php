<?php

declare(strict_types=1);

use App\Support\Markdown;

it('renders @[youtube](url) as a responsive iframe', function (): void {
    $content = "Some text\n\n@[youtube](https://www.youtube.com/watch?v=dQw4w9WgXcQ)\n\nMore text";

    $html = Markdown::render($content);

    expect($html)
        ->toContain('https://www.youtube.com/embed/dQw4w9WgXcQ')
        ->toContain('<iframe')
        ->toContain('allowfullscreen')
        ->toContain('padding-bottom:56.25%');
});

it('handles multiple youtube embeds in one article', function (): void {
    $content = "@[youtube](https://www.youtube.com/watch?v=dQw4w9WgXcQ)\n\nSome text\n\n@[youtube](https://youtu.be/jNQXAC9IVRw)";

    $html = Markdown::render($content);

    expect($html)
        ->toContain('https://www.youtube.com/embed/dQw4w9WgXcQ')
        ->toContain('https://www.youtube.com/embed/jNQXAC9IVRw');
});

it('renders invalid youtube URLs as plain text', function (): void {
    $content = '@[youtube](https://example.com/not-a-video)';

    $html = Markdown::render($content);

    expect($html)
        ->not->toContain('<iframe')
        ->toContain('https://example.com/not-a-video');
});

it('strips @[youtube] lines from plain text output', function (): void {
    $content = "Some text\n\n@[youtube](https://www.youtube.com/watch?v=dQw4w9WgXcQ)\n\nMore text";

    $plainText = Markdown::toPlainText($content);

    expect($plainText)
        ->toContain('Some text')
        ->toContain('More text')
        ->not->toContain('youtube')
        ->not->toContain('dQw4w9WgXcQ');
});

it('handles youtu.be short URLs', function (): void {
    $html = Markdown::render('@[youtube](https://youtu.be/dQw4w9WgXcQ)');

    expect($html)->toContain('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('handles youtube shorts URLs', function (): void {
    $html = Markdown::render('@[youtube](https://www.youtube.com/shorts/dQw4w9WgXcQ)');

    expect($html)->toContain('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('handles youtube embed URLs', function (): void {
    $html = Markdown::render('@[youtube](https://www.youtube.com/embed/dQw4w9WgXcQ)');

    expect($html)->toContain('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

it('handles youtube live URLs', function (): void {
    $html = Markdown::render('@[youtube](https://www.youtube.com/live/dQw4w9WgXcQ)');

    expect($html)->toContain('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

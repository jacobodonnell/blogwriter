<?php

declare(strict_types=1);

use App\Support\Markdown;

it('renders plain images unchanged', function () {
    $html = Markdown::render('![alt text](https://example.com/img.jpg)');
    expect($html)->toContain('<img')
        ->toContain('src="https://example.com/img.jpg"')
        ->toContain('alt="alt text"')
        ->not->toContain('img-align-')
        ->not->toContain('<figure');
});

it('applies align:center class on wrapper div', function () {
    $html = Markdown::render('![My image|align:center](https://example.com/img.jpg)');
    expect($html)->toContain('<div class="img-align-center">')
        ->toContain('src="https://example.com/img.jpg"')
        ->toContain('alt="My image"');
});

it('applies align:left class on wrapper div', function () {
    $html = Markdown::render('![Photo|align:left](https://example.com/img.jpg)');
    expect($html)->toContain('<div class="img-align-left">');
});

it('applies align:right class on wrapper div', function () {
    $html = Markdown::render('![Photo|align:right](https://example.com/img.jpg)');
    expect($html)->toContain('<div class="img-align-right">');
});

it('applies align:full class on wrapper div', function () {
    $html = Markdown::render('![Photo|align:full](https://example.com/img.jpg)');
    expect($html)->toContain('<div class="img-align-full">');
});

it('applies width style', function () {
    $html = Markdown::render('![Image|width:400](https://example.com/img.jpg)');
    expect($html)->toContain('style="width:400px;max-width:100%"');
});

it('applies percentage width style', function () {
    $html = Markdown::render('![Image|width:50%](https://example.com/img.jpg)');
    expect($html)->toContain('style="width:50%;max-width:100%"');
});

it('wraps in figure with figcaption when caption present', function () {
    $html = Markdown::render('![Image|caption:A%20caption](https://example.com/img.jpg)');
    expect($html)->toContain('<figure')
        ->toContain('<figcaption>A caption</figcaption>')
        ->toContain('<img');
});

it('combines align width and caption', function () {
    $html = Markdown::render('![Photo|align:center|width:500|caption:Nice%20photo](https://example.com/img.jpg)');
    expect($html)->toContain('class="img-align-center"')
        ->toContain('style="width:500px;max-width:100%"')
        ->toContain('<figure')
        ->toContain('<figcaption>Nice photo</figcaption>')
        ->not->toContain('<div class="img-align-center">');
});

it('strips extended images from plain text', function () {
    $text = Markdown::toPlainText("Hello\n\n![Image|align:center|caption:A%20caption](https://example.com/img.jpg)\n\nWorld");
    expect($text)->toContain('Hello')
        ->toContain('World')
        ->not->toContain('align:center')
        ->not->toContain('img-align')
        ->not->toContain('caption');
});

it('rejects invalid align values', function () {
    $html = Markdown::render('![Image|align:javascript](https://example.com/img.jpg)');
    expect($html)->not->toContain('img-align-javascript')
        ->not->toContain('class=');
});

it('escapes XSS in caption', function () {
    $caption = urlencode('<script>alert(1)</script>');
    $html = Markdown::render("![Image|caption:{$caption}](https://example.com/img.jpg)");
    expect($html)->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

it('handles CRLF line endings', function () {
    $html = Markdown::render("![Image|align:center](https://example.com/img.jpg)\r\n\r\nSome text");
    expect($html)->toContain('<div class="img-align-center">')
        ->toContain('Some text');
});

it('handles plain alt text with no extended attributes gracefully', function () {
    $html = Markdown::render('![just alt](https://example.com/img.jpg)');
    expect($html)->toContain('alt="just alt"')
        ->not->toContain('<figure')
        ->not->toContain('img-align-');
});

it('strips backtick wrappers from caption', function () {
    $caption = urlencode('`A caption`');
    $html = Markdown::render("![Image|caption:{$caption}](https://example.com/img.jpg)");
    expect($html)->toContain('<figcaption>A caption</figcaption>')
        ->not->toContain('`');
});

it('strips backtick-wrapped caption with quotes inside', function () {
    $caption = urlencode('`Photo: "Mountain & Valley"`');
    $html = Markdown::render("![Image|caption:{$caption}](https://example.com/img.jpg)");
    expect($html)->toContain('<figcaption>')
        ->toContain('Mountain')
        ->not->toContain('`Photo')
        ->not->toContain('`</figcaption>');
});

it('decodes URL-encoded captions with special characters', function () {
    $caption = urlencode('Photo: "Mountain & Valley"');
    $html = Markdown::render("![Image|caption:{$caption}](https://example.com/img.jpg)");
    expect($html)->toContain('<figcaption>')
        ->toContain('Mountain')
        ->not->toContain('<script');
});

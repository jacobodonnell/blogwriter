<?php

use App\Models\Article;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('opens the BlogWriter footer link in a new tab', function (): void {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $content = $response->getContent();

    preg_match('/<a[^>]*href="https:\/\/blogwriter\.tech"[^>]*>/', $content, $matches);
    expect($matches)->not->toBeEmpty();
    expect($matches[0])->toContain('target="_blank"');
});

it('opens the Twitter share link in a new tab on article pages', function (): void {
    $article = Article::factory()->published()->create();

    $response = $this->get($article->permalink());

    $response->assertSuccessful();
    $content = $response->getContent();

    preg_match('/<a[^>]*href="https:\/\/twitter\.com\/intent\/tweet[^"]*"[^>]*>/', $content, $matches);
    expect($matches)->not->toBeEmpty();
    expect($matches[0])->toContain('target="_blank"');
});

it('opens the Twitter share link in a new tab on photo pages', function (): void {
    $photo = Photo::factory()->published()->create();

    $response = $this->get(route('photos.show', $photo->slug));

    $response->assertSuccessful();
    $content = $response->getContent();

    preg_match('/<a[^>]*href="https:\/\/twitter\.com\/intent\/tweet[^"]*"[^>]*>/', $content, $matches);
    expect($matches)->not->toBeEmpty();
    expect($matches[0])->toContain('target="_blank"');
});

it('opens all external links in markdown content in a new tab', function (): void {
    $photo = Photo::factory()->published()->create([
        'caption' => 'Photo by [John](https://unsplash.com/photos/123)',
    ]);

    $article = Article::factory()->published()->create([
        'content' => 'Check out [Example](https://example.com) for more.',
    ]);

    $appHost = parse_url(config('app.url'), PHP_URL_HOST);

    // Photo show page
    $response = $this->get(route('photos.show', $photo->slug));
    $response->assertSuccessful();
    preg_match_all('/<a[^>]*href="([^"]*)"[^>]*>/', $response->getContent(), $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $href = $match[1];
        $linkHost = parse_url($href, PHP_URL_HOST);

        if ($linkHost !== null && $linkHost !== $appHost) {
            expect($match[0])->toContain('target="_blank"');
        }
    }

    // Article show page
    $response = $this->get($article->permalink());
    $response->assertSuccessful();
    preg_match_all('/<a[^>]*href="([^"]*)"[^>]*>/', $response->getContent(), $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $href = $match[1];
        $linkHost = parse_url($href, PHP_URL_HOST);

        if ($linkHost !== null && $linkHost !== $appHost) {
            expect($match[0])->toContain('target="_blank"');
        }
    }
});

it('does not open internal links in markdown content in a new tab', function (): void {
    $appUrl = config('app.url');

    $photo = Photo::factory()->published()->create([
        'caption' => "Visit [Photos]({$appUrl}/photos) for more.",
    ]);

    $article = Article::factory()->published()->create([
        'content' => "Go to [Home]({$appUrl}) to start.",
    ]);

    $appHost = parse_url($appUrl, PHP_URL_HOST);

    // Photo show page
    $response = $this->get(route('photos.show', $photo->slug));
    $response->assertSuccessful();
    preg_match_all('/<a[^>]*href="([^"]*)"[^>]*>/', $response->getContent(), $matches, PREG_SET_ORDER);

    $foundInternalMarkdownLink = false;

    foreach ($matches as $match) {
        $href = $match[1];
        $linkHost = parse_url($href, PHP_URL_HOST);

        if ($linkHost === $appHost && str_contains($href, '/photos')) {
            $foundInternalMarkdownLink = true;
            expect($match[0])->not->toContain('target="_blank"');
        }
    }

    expect($foundInternalMarkdownLink)->toBeTrue('Expected to find an internal markdown link on photo page');

    // Article show page
    $response = $this->get($article->permalink());
    $response->assertSuccessful();
    preg_match_all('/<a[^>]*href="([^"]*)"[^>]*>/', $response->getContent(), $matches, PREG_SET_ORDER);

    $foundInternalMarkdownLink = false;

    foreach ($matches as $match) {
        $href = $match[1];
        $linkHost = parse_url($href, PHP_URL_HOST);

        if ($linkHost === $appHost && $href === $appUrl) {
            $foundInternalMarkdownLink = true;
            expect($match[0])->not->toContain('target="_blank"');
        }
    }

    expect($foundInternalMarkdownLink)->toBeTrue('Expected to find an internal markdown link on article page');
});

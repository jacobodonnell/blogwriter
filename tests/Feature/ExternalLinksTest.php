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

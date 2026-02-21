<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;

// RSS Feed

it('returns RSS feed with correct content type', function (): void {
    $this->get('/feed')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

it('returns valid RSS XML structure', function (): void {
    Article::factory()->published()->create(['title' => 'RSS Test Article']);

    $response = $this->get('/feed');
    $response->assertSuccessful();

    $content = $response->getContent();
    $this->assertStringContainsString('<?xml version="1.0"', $content);
    $this->assertStringContainsString('<rss version="2.0"', $content);
    $this->assertStringContainsString('<channel>', $content);
    $this->assertStringContainsString('RSS Test Article', $content);
});

it('includes full content in RSS content:encoded', function (): void {
    Article::factory()->published()->create([
        'title' => 'Full Content Article',
        'content' => 'This is the **full** article content.',
    ]);

    $response = $this->get('/feed');
    $content = $response->getContent();

    $this->assertStringContainsString('content:encoded', $content);
    $this->assertStringContainsString('full', $content);
});

it('serves RSS feed at /rss alias', function (): void {
    $this->get('/rss')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

// Atom Feed

it('returns Atom feed with correct content type', function (): void {
    $this->get('/atom')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8');
});

it('returns valid Atom XML structure', function (): void {
    Article::factory()->published()->create(['title' => 'Atom Test Article']);

    $response = $this->get('/atom');
    $content = $response->getContent();

    $this->assertStringContainsString('<?xml version="1.0"', $content);
    $this->assertStringContainsString('<feed xmlns="http://www.w3.org/2005/Atom"', $content);
    $this->assertStringContainsString('<entry>', $content);
    $this->assertStringContainsString('Atom Test Article', $content);
});

// JSON Feed

it('returns JSON feed with correct content type', function (): void {
    $this->get('/feed.json')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/feed+json; charset=UTF-8');
});

it('returns valid JSON Feed 1.1 structure', function (): void {
    Article::factory()->published()->create(['title' => 'JSON Test Article']);

    $response = $this->get('/feed.json');
    $response->assertSuccessful();

    $data = $response->json();

    expect($data)
        ->toHaveKey('version', 'https://jsonfeed.org/version/1.1')
        ->toHaveKey('title')
        ->toHaveKey('home_page_url')
        ->toHaveKey('feed_url')
        ->toHaveKey('items');

    expect($data['items'])->toHaveCount(1);
    expect($data['items'][0])
        ->toHaveKey('id')
        ->toHaveKey('url')
        ->toHaveKey('title', 'JSON Test Article')
        ->toHaveKey('content_html')
        ->toHaveKey('date_published');
});

// Published vs Draft filtering

it('shows published articles in feeds', function (): void {
    $article = Article::factory()->published()->create(['title' => 'Published Feed Article']);

    $this->get('/feed')->assertSee('Published Feed Article');
    $this->get('/atom')->assertSee('Published Feed Article');

    $json = $this->get('/feed.json')->json();
    expect(collect($json['items'])->pluck('title'))->toContain('Published Feed Article');
});

it('excludes draft articles from feeds', function (): void {
    Article::factory()->draft()->create(['title' => 'Secret Draft Article']);

    $this->get('/feed')->assertDontSee('Secret Draft Article');
    $this->get('/atom')->assertDontSee('Secret Draft Article');

    $json = $this->get('/feed.json')->json();
    expect(collect($json['items'])->pluck('title'))->not->toContain('Secret Draft Article');
});

it('shows published photos in feeds', function (): void {
    $photo = Photo::factory()->published()->create([
        'alt_text' => 'Feed Photo Alt Text',
        'caption' => 'A beautiful sunset photo.',
    ]);

    $this->get('/feed')->assertSee('Feed Photo Alt Text');
    $this->get('/atom')->assertSee('Feed Photo Alt Text');

    $json = $this->get('/feed.json')->json();
    expect(collect($json['items'])->pluck('title'))->toContain('Feed Photo Alt Text');
});

it('excludes draft photos from feeds', function (): void {
    Photo::factory()->draft()->create([
        'alt_text' => 'Draft Photo Should Not Appear',
    ]);

    $this->get('/feed')->assertDontSee('Draft Photo Should Not Appear');
    $this->get('/atom')->assertDontSee('Draft Photo Should Not Appear');
});

// Ordering

it('orders items by published_at descending', function (): void {
    Article::factory()->published()->create([
        'title' => 'Old Article',
        'published_at' => now()->subDays(10),
    ]);
    Article::factory()->published()->create([
        'title' => 'New Article',
        'published_at' => now(),
    ]);

    $this->get('/feed')
        ->assertSeeInOrder(['New Article', 'Old Article']);

    $json = $this->get('/feed.json')->json();
    $titles = collect($json['items'])->pluck('title')->toArray();
    expect($titles)->toBe(['New Article', 'Old Article']);
});

// Mixed content types

it('merges articles and photos in feeds', function (): void {
    Article::factory()->published()->create([
        'title' => 'Mixed Article',
        'published_at' => now()->subDay(),
    ]);
    Photo::factory()->published()->create([
        'alt_text' => 'Mixed Photo',
        'published_at' => now(),
    ]);

    $json = $this->get('/feed.json')->json();
    expect($json['items'])->toHaveCount(2);
});

// Empty feeds

it('returns valid RSS with no items', function (): void {
    $response = $this->get('/feed');
    $response->assertSuccessful();

    $content = $response->getContent();
    $this->assertStringContainsString('<rss version="2.0"', $content);
    $this->assertStringContainsString('<channel>', $content);
});

it('returns valid Atom with no items', function (): void {
    $response = $this->get('/atom');
    $response->assertSuccessful();

    $content = $response->getContent();
    $this->assertStringContainsString('<feed xmlns="http://www.w3.org/2005/Atom"', $content);
});

it('returns valid JSON Feed with no items', function (): void {
    $json = $this->get('/feed.json')->json();

    expect($json)
        ->toHaveKey('version', 'https://jsonfeed.org/version/1.1')
        ->toHaveKey('items');
    expect($json['items'])->toBeEmpty();
});

// Category support

it('includes category in feed items', function (): void {
    $category = Category::factory()->create(['name' => 'Technology']);
    Article::factory()->published()->create([
        'title' => 'Categorized Article',
        'category_id' => $category->id,
    ]);

    $this->get('/feed')->assertSee('Technology');
    $this->get('/atom')->assertSee('Technology');

    $json = $this->get('/feed.json')->json();
    expect($json['items'][0]['tags'])->toContain('Technology');
});

// Feed discovery

it('includes feed discovery links on public pages', function (): void {
    $response = $this->get('/');
    $content = $response->getContent();

    $this->assertStringContainsString('application/rss+xml', $content);
    $this->assertStringContainsString('application/atom+xml', $content);
    $this->assertStringContainsString('application/feed+json', $content);
});

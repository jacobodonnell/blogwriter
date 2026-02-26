<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Spatie\ResponseCache\Events\ClearedResponseCacheEvent;
use Spatie\ResponseCache\Events\ResponseCacheHitEvent;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(function (): void {
    config(['responsecache.enabled' => true]);
    ResponseCache::clear();
});

afterEach(function (): void {
    ResponseCache::clear();
});

it('caches the response for a guest on a second request', function (): void {
    Event::fake([ResponseCacheHitEvent::class]);

    $this->get('/');
    $this->get('/');

    Event::assertDispatched(ResponseCacheHitEvent::class);
});

it('does not serve cached page to an authenticated user', function (): void {
    Event::fake([ResponseCacheHitEvent::class]);

    $user = User::factory()->create();

    // Seed the cache as a guest
    $this->get('/');

    // Authenticated request should bypass the cache entirely
    $this->actingAs($user)->get('/');

    Event::assertNotDispatched(ResponseCacheHitEvent::class);
});

it('flushes cache when an article public column changes', function (): void {
    $article = Article::factory()->published()->create();

    Event::fake([ClearedResponseCacheEvent::class]);

    $article->update(['title' => 'Updated Title']);

    Event::assertDispatched(ClearedResponseCacheEvent::class);
});

it('does not flush cache when only the draft column changes', function (): void {
    $article = Article::factory()->published()->create();

    Event::fake([ClearedResponseCacheEvent::class]);

    // Autosave: only touches the draft column — must NOT flush
    $article->update(['draft' => ['title' => 'Draft Title']]);

    Event::assertNotDispatched(ClearedResponseCacheEvent::class);
});

it('flushes cache when an article is created', function (): void {
    Event::fake([ClearedResponseCacheEvent::class]);

    Article::factory()->published()->create();

    Event::assertDispatched(ClearedResponseCacheEvent::class);
});

it('flushes cache when an article is deleted', function (): void {
    $article = Article::factory()->published()->create();

    Event::fake([ClearedResponseCacheEvent::class]);

    $article->delete();

    Event::assertDispatched(ClearedResponseCacheEvent::class);
});

it('flushes cache when a photo is saved', function (): void {
    Event::fake([ClearedResponseCacheEvent::class]);

    Photo::factory()->published()->create();

    Event::assertDispatched(ClearedResponseCacheEvent::class);
});

it('flushes cache when a category is saved', function (): void {
    Event::fake([ClearedResponseCacheEvent::class]);

    Category::factory()->create();

    Event::assertDispatched(ClearedResponseCacheEvent::class);
});

it('does not cache admin routes', function (): void {
    Event::fake([ResponseCacheHitEvent::class]);

    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin');
    $this->actingAs($user)->get('/admin');

    Event::assertNotDispatched(ResponseCacheHitEvent::class);
});

it('does not cache feed routes via Spatie ResponseCache', function (): void {
    Event::fake([ResponseCacheHitEvent::class]);

    $this->get('/feed');
    $this->get('/feed');

    Event::assertNotDispatched(ResponseCacheHitEvent::class);
});

<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('displays the dashboard for authenticated users', function (): void {
    $response = $this->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Dashboard');
});

it('redirects unauthenticated users', function (): void {
    auth()->logout();

    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect();
});

it('shows correct article stats', function (): void {
    Article::factory()->published()->count(3)->create();
    Article::factory()->draft()->count(2)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('stats', function (array $stats): bool {
        return $stats['total_articles'] === 5
            && $stats['published_articles'] === 3
            && $stats['draft_articles'] === 2;
    });
});

it('shows correct photo stats', function (): void {
    Photo::factory()->published()->count(4)->create();
    Photo::factory()->draft()->count(1)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('stats', function (array $stats): bool {
        return $stats['total_photos'] === 5
            && $stats['published_photos'] === 4
            && $stats['draft_photos'] === 1;
    });
});

it('shows correct category count', function (): void {
    $names = ['Technology', 'Travel', 'Design'];
    foreach ($names as $name) {
        Category::factory()->create(['name' => $name]);
    }

    $response = $this->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('stats', function (array $stats): bool {
        return $stats['categories'] === 3;
    });
});

it('caps recent articles at 5', function (): void {
    Article::factory()->count(8)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('recentArticles', fn ($articles) => $articles->count() === 5);
});

it('caps recent photos at 4', function (): void {
    Photo::factory()->count(8)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('recentPhotos', fn ($photos) => $photos->count() === 4);
});

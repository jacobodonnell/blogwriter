<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Route;

pest()->group('slow');

it('enforces link target rule on all pages', function (): void {
    $user = User::factory()->create();

    $article = Article::factory()->published()->create([
        'content' => 'Check out [Example](https://example.com) for more.',
    ]);

    $photo = Photo::factory()->published()->create([
        'caption' => 'Photo by [Unsplash](https://unsplash.com/photos/123)',
    ]);

    $category = Category::factory()->create();

    Setting::set('profile_github', 'https://github.com/testuser');
    Setting::set('profile_mastodon', 'https://mastodon.social/@testuser');
    Setting::set('profile_bluesky', 'https://bsky.app/profile/testuser');
    Setting::set('profile_email', 'test@example.com');

    $this->actingAs($user);

    $appHost = parse_url(config('app.url'), PHP_URL_HOST);

    $skipRoutes = [
        'fallback',
        'feed.rss',
        'feed.atom',
        'feed.json',
        'feed.rss.alias',
        'robots.txt',
        'install',
        'admin.media.show',
        'admin.articles.download',
        'admin.photos.download',
        'admin.categories.explore.show',
        'storage.local',
        'password.confirm',
        'password.confirmation',
    ];

    $paramMap = [
        'article' => $article,
        'photo' => $photo,
        'category' => $category,
        'slug' => fn (string $routeName): string => str_contains($routeName, 'photo')
            ? $photo->slug
            : $article->slug,
    ];

    $urls = collect(Route::getRoutes())
        ->filter(fn ($route) => in_array('GET', $route->methods()))
        ->reject(fn ($route) => in_array($route->getName(), $skipRoutes))
        ->reject(fn ($route) => $route->getName() === null)
        ->map(function ($route) use ($paramMap): ?string {
            try {
                $params = [];
                foreach ($route->parameterNames() as $param) {
                    if (isset($paramMap[$param])) {
                        $value = $paramMap[$param];
                        $params[$param] = is_callable($value) ? $value($route->getName()) : $value;
                    } else {
                        return null;
                    }
                }

                return route($route->getName(), $params);
            } catch (Throwable) {
                return null;
            }
        })
        ->filter();

    $violations = [];

    foreach ($urls as $url) {
        $response = $this->get($url);

        if (! $response->isSuccessful()) {
            continue;
        }

        preg_match_all('/<a\b([^>]*)>/i', $response->getContent(), $tags, PREG_SET_ORDER);

        foreach ($tags as $tag) {
            $tagHtml = '<a'.$tag[1].'>';

            preg_match('/\bhref="([^"]*)"/i', $tagHtml, $hrefMatch);

            if (empty($hrefMatch)) {
                continue;
            }

            $href = $hrefMatch[1];

            if (str_starts_with($href, 'mailto:')) {
                continue;
            }

            $linkHost = parse_url($href, PHP_URL_HOST);
            $hasBlank = (bool) preg_match('/\btarget="_blank"/i', $tagHtml);
            $isExternal = $linkHost !== null && $linkHost !== $appHost;

            if ($isExternal && ! $hasBlank) {
                $violations[] = "MISSING target=\"_blank\" on [{$url}]: {$tagHtml}";
            } elseif (! $isExternal && $hasBlank) {
                $violations[] = "UNEXPECTED target=\"_blank\" on [{$url}]: {$tagHtml}";
            }
        }
    }

    expect($violations)->toBeEmpty(implode("\n", $violations));
});

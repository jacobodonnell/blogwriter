<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Photo;
use App\Models\Setting;
use App\Models\User;

it('enforces link target rule on all pages', function (): void {
    $user = User::factory()->create();

    $article = Article::factory()->published()->create([
        'content' => 'Check out [Example](https://example.com) for more.',
    ]);

    $photo = Photo::factory()->published()->create([
        'caption' => 'Photo by [Unsplash](https://unsplash.com/photos/123)',
    ]);

    Setting::set('profile_github', 'https://github.com/testuser');
    Setting::set('profile_mastodon', 'https://mastodon.social/@testuser');
    Setting::set('profile_bluesky', 'https://bsky.app/profile/testuser');
    Setting::set('profile_email', 'test@example.com');

    $this->actingAs($user);

    $appHost = parse_url(config('app.url'), PHP_URL_HOST);

    $pages = [
        route('home'),
        route('about'),
        route('articles.index'),
        $article->permalink(),
        route('photos.index'),
        route('photos.show', $photo->slug),
        route('admin.articles.index'),
        route('admin.photos.index'),
        route('admin.categories.index'),
        route('admin.articles.edit', $article),
    ];

    $violations = [];

    foreach ($pages as $url) {
        $response = $this->get($url);
        $response->assertSuccessful();

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

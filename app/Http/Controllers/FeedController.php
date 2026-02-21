<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class FeedController extends Controller
{
    public function __construct(
        private readonly FeedService $feedService,
    ) {}

    /**
     * RSS 2.0 feed.
     */
    public function rss(): Response
    {
        $items = $this->feedService->getItems();
        $lastUpdated = $this->feedService->getLastUpdated($items);

        return response()
            ->view('feeds.rss', [
                'items' => $items,
                'lastUpdated' => $lastUpdated,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Atom 1.0 feed.
     */
    public function atom(): Response
    {
        $items = $this->feedService->getItems();
        $lastUpdated = $this->feedService->getLastUpdated($items);

        return response()
            ->view('feeds.atom', [
                'items' => $items,
                'lastUpdated' => $lastUpdated,
            ])
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }

    /**
     * JSON Feed 1.1.
     */
    public function json(): JsonResponse
    {
        $items = $this->feedService->getItems();

        $feed = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => config('app.name', 'BlogWriter'),
            'home_page_url' => route('home'),
            'feed_url' => route('feed.json'),
            'description' => setting('profile_bio', ''),
            'items' => $items->map(function ($item): array {
                $entry = [
                    'id' => $item->id,
                    'url' => $item->url,
                    'title' => $item->title,
                    'content_html' => $item->contentHtml,
                    'date_published' => $item->publishedAt->toRfc3339String(),
                    'authors' => [['name' => $item->authorName]],
                ];

                if ($item->summary !== '' && $item->summary !== '0') {
                    $entry['summary'] = $item->summary;
                }

                if ($item->updatedAt instanceof \Carbon\CarbonInterface) {
                    $entry['date_modified'] = $item->updatedAt->toRfc3339String();
                }

                if ($item->imageUrl) {
                    $entry['image'] = $item->imageUrl;
                }

                if ($item->categoryName) {
                    $entry['tags'] = [$item->categoryName];
                }

                return $entry;
            })->values()->toArray(),
        ];

        return response()->json($feed)
            ->header('Content-Type', 'application/feed+json; charset=UTF-8');
    }
}

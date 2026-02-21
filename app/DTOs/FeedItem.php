<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\CarbonInterface;

final readonly class FeedItem
{
    public function __construct(
        public string $title,
        public string $url,
        public string $id,
        public string $contentHtml,
        public string $summary,
        public string $authorName,
        public CarbonInterface $publishedAt,
        public ?CarbonInterface $updatedAt = null,
        public ?string $imageUrl = null,
        public ?string $categoryName = null,
        public string $contentType = 'article',
    ) {}
}

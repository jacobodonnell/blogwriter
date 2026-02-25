<?php

declare(strict_types=1);

namespace App\Support;

final readonly class ParsedImport
{
    /**
     * @param  array<int, array{frontmatter: array<string, mixed>, content: string}>  $articles
     * @param  array<int, array<string, mixed>>|null  $categoriesYaml
     * @param  array<string, mixed>|null  $settingsYaml
     * @param  array<int, array<string, mixed>>|null  $photosYaml
     */
    public function __construct(
        public array $articles,
        public ?array $categoriesYaml,
        public ?array $settingsYaml = null,
        public ?array $photosYaml = null,
        public ?string $imagesTempDir = null,
    ) {}
}

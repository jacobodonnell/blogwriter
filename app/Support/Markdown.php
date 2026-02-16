<?php

namespace App\Support;

use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

class Markdown
{
    /**
     * Render markdown with external links opening in a new tab.
     *
     * @param  array<string, mixed>  $options
     */
    public static function render(string $content, array $options = []): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        $defaults = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => [$host],
                'open_in_new_window' => true,
                'noopener' => 'external',
            ],
        ];

        return Str::markdown($content, array_merge($defaults, $options), [
            new ExternalLinkExtension,
        ]);
    }
}

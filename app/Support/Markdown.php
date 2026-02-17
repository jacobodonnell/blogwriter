<?php

namespace App\Support;

use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

class Markdown
{
    /**
     * Convert markdown to plain text by rendering to HTML then stripping tags.
     */
    public static function toPlainText(string $content): string
    {
        $html = Str::markdown($content);
        $html = preg_replace('/<\/(h[1-6]|p|li|blockquote|div|tr)>/', '$0 ', $html);

        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/', ' ', $text));
    }

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

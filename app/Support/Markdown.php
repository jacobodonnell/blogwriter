<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

final class Markdown
{
    private const YOUTUBE_EMBED_PATTERN = '/^@\[youtube\]\(([^)]+)\)$/m';

    /**
     * Convert markdown to plain text by rendering to HTML then stripping tags.
     * Strips @[youtube](...) lines so they don't appear in excerpts/meta descriptions.
     */
    public static function toPlainText(string $content): string
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = preg_replace(self::YOUTUBE_EMBED_PATTERN, '', $content);

        $html = Str::markdown((string) $content);
        $html = preg_replace('/<\/(h[1-6]|p|li|blockquote|div|tr)>/', '$0 ', $html);

        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return mb_trim((string) preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Render markdown with external links opening in a new tab.
     * Converts @[youtube](url) to responsive iframe embeds.
     *
     * @param  array<string, mixed>  $options
     */
    public static function render(string $content, array $options = []): string
    {
        $content = str_replace("\r\n", "\n", $content);
        [$content, $embeds] = self::extractVideoEmbeds($content);

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

        $html = Str::markdown($content, array_merge($defaults, $options), [
            new ExternalLinkExtension,
        ]);

        return self::restoreVideoEmbeds($html, $embeds);
    }

    /**
     * Extract @[youtube](url) embeds and replace with placeholders.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    private static function extractVideoEmbeds(string $content): array
    {
        $embeds = [];
        $index = 0;

        $content = preg_replace_callback(self::YOUTUBE_EMBED_PATTERN, function (array $matches) use (&$embeds, &$index): string {
            $embeds[$index] = $matches[1];

            return 'VIDEO_EMBED_'.$index++.'_PLACEHOLDER';
        }, $content);

        return [(string) $content, $embeds];
    }

    /**
     * Replace placeholder paragraphs with responsive iframe embeds.
     *
     * @param  array<int, string>  $embeds
     */
    private static function restoreVideoEmbeds(string $html, array $embeds): string
    {
        foreach ($embeds as $index => $url) {
            $embedUrl = self::youtubeEmbedUrl($url);

            if ($embedUrl === null) {
                $html = str_replace(
                    '<p>VIDEO_EMBED_'.$index.'_PLACEHOLDER</p>',
                    '<p>'.e($url).'</p>',
                    $html,
                );

                continue;
            }

            $iframe = '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden">'
                .'<iframe src="'.e($embedUrl).'" style="position:absolute;top:0;left:0;width:100%;height:100%" '
                .'frameborder="0" allowfullscreen loading="lazy"></iframe>'
                .'</div>';

            $html = str_replace('<p>VIDEO_EMBED_'.$index.'_PLACEHOLDER</p>', $iframe, $html);
        }

        return $html;
    }

    /**
     * Extract YouTube video ID from various URL formats and return the embed URL.
     *
     * Supports: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID,
     * youtube.com/shorts/ID, youtube.com/live/ID
     */
    private static function youtubeEmbedUrl(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtu\.be\/)([\w-]{11})/';

        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return null;
    }
}

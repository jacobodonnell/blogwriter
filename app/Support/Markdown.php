<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

final class Markdown
{
    private const YOUTUBE_EMBED_PATTERN = '/^@\[youtube\]\(([^)]+)\)$/m';

    private const EXTENDED_IMAGE_PATTERN = '/!\[([^\]]*\|[^\]]*)\]\(([^)]+)\)/';

    /**
     * Convert markdown to plain text by rendering to HTML then stripping tags.
     * Strips @[youtube](...) lines and extended images so they don't appear in excerpts/meta descriptions.
     */
    public static function toPlainText(string $content): string
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = preg_replace(self::YOUTUBE_EMBED_PATTERN, '', $content);
        $content = preg_replace(self::EXTENDED_IMAGE_PATTERN, '', (string) $content);

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
        [$content, $images] = self::extractExtendedImages($content);

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

        $html = self::restoreVideoEmbeds($html, $embeds);

        return self::restoreExtendedImages($html, $images);
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

    /**
     * Extract extended image syntax ![alt|key:val](url) and replace with placeholders.
     *
     * @return array{0: string, 1: array<int, array{alt: string, src: string}>}
     */
    private static function extractExtendedImages(string $content): array
    {
        $images = [];
        $index = 0;

        $content = preg_replace_callback(self::EXTENDED_IMAGE_PATTERN, function (array $matches) use (&$images, &$index): string {
            $images[$index] = ['alt' => $matches[1], 'src' => $matches[2]];

            return 'IMAGE_EMBED_'.$index++.'_PLACEHOLDER';
        }, $content);

        return [(string) $content, $images];
    }

    /**
     * Parse the extended alt string (e.g. "Alt text|align:center|width:50%|caption:Hello") into its components.
     *
     * @return array{alt: string, align: string|null, width: string|null, caption: string|null}
     */
    private static function parseExtendedImageAlt(string $rawAlt): array
    {
        $parts = explode('|', $rawAlt);
        $alt = array_shift($parts);
        $align = null;
        $width = null;
        $caption = null;

        $allowedAligns = ['left', 'center', 'right', 'full'];

        foreach ($parts as $part) {
            $colonIdx = mb_strpos($part, ':');
            if ($colonIdx === false) {
                continue;
            }

            $key = mb_trim(mb_substr($part, 0, $colonIdx));
            $value = mb_trim(mb_substr($part, $colonIdx + 1));

            match ($key) {
                'align' => $align = in_array($value, $allowedAligns, true) ? $value : null,
                'width' => $width = self::parseWidth($value),
                'caption' => $caption = self::stripBackticks(urldecode($value)),
                default => null,
            };
        }

        return ['alt' => $alt, 'align' => $align, 'width' => $width, 'caption' => $caption];
    }

    /**
     * Strip one leading and one trailing backtick if both are present.
     */
    private static function stripBackticks(string $value): string
    {
        if (str_starts_with($value, '`') && str_ends_with($value, '`') && mb_strlen($value) >= 2) {
            return mb_substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Parse a width value — supports percentage (e.g. "50%") or pixel integers (e.g. "400").
     */
    private static function parseWidth(string $value): ?string
    {
        if (preg_match('/^\d+%$/', $value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value.'px';
        }

        return null;
    }

    /**
     * Replace IMAGE_EMBED_N_PLACEHOLDER paragraphs with rendered image/figure HTML.
     *
     * @param  array<int, array{alt: string, src: string}>  $images
     */
    private static function restoreExtendedImages(string $html, array $images): string
    {
        foreach ($images as $index => $image) {
            ['alt' => $rawAlt, 'src' => $src] = $image;
            ['alt' => $alt, 'align' => $align, 'width' => $width, 'caption' => $caption] = self::parseExtendedImageAlt($rawAlt);

            $class = $align ? 'img-align-'.$align : null;

            $figureStyle = $width ? '--figure-width:'.$width : null;

            $imgTag = '<img src="'.e($src).'" alt="'.e($alt).'">';

            if ($caption || $width) {
                $figureAttrs = '';
                if ($class) {
                    $figureAttrs .= ' class="'.e($class).'"';
                }

                if ($figureStyle) {
                    $figureAttrs .= ' style="'.e($figureStyle).'"';
                }

                $captionHtml = $caption ? '<figcaption>'.e($caption).'</figcaption>' : '';
                $replacement = '<figure'.$figureAttrs.'>'.$imgTag.$captionHtml.'</figure>';
            } elseif ($class) {
                $replacement = '<div class="'.e($class).'">'.$imgTag.'</div>';
            } else {
                $replacement = $imgTag;
            }

            $html = str_replace('<p>IMAGE_EMBED_'.$index.'_PLACEHOLDER</p>', $replacement, $html);
        }

        return $html;
    }
}

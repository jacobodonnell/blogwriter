@props(['url', 'text'])

<div class="flex flex-wrap gap-3">
    <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($text) }}"
       target="_blank"
       rel="noopener"
       class="btn btn-sm btn-outline gap-2">
        <i class="ph ph-twitter-logo"></i>
        Share on Twitter
    </a>
    <x-copy-link-button :url="$url" />
</div>

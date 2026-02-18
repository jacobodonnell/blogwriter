@props([
    'title' => null,
    'description' => null,
    'url' => null,
    'image' => null,
    'type' => 'website',
    'article' => null,
    'photo' => null,
])

@php
    $siteName = config('app.name', 'BlogWriter');
    $siteUrl = config('app.url');

    // Resolve values with smart defaults
    $metaTitle = $title ?? $siteName;
    $metaDescription = $description ?? setting('profile_bio', '');
    $metaUrl = $url ?? url()->current();
    $metaImage = $image ?? placeholder_image_url();
    $metaType = $type;

    // Article overrides
    if ($article) {
        $metaTitle = $article->meta_title ?? $article->title;
        $metaDescription = $article->meta_description ?? $article->excerpt;
        $metaImage = $article->og_image ?? $metaImage;
        $metaUrl = route('articles.show', $article->slug);
        $metaType = 'article';
    }

    // Photo overrides
    if ($photo) {
        $metaTitle = $photo->alt_text;
        $metaDescription = $photo->caption ? Str::limit(strip_tags($photo->caption), 160) : $photo->alt_text;
        $metaImage = $photo->image_url;
        $metaUrl = route('photos.show', $photo->slug);
    }

    // Ensure image is absolute
    if ($metaImage && !str_starts_with($metaImage, 'http')) {
        $metaImage = url($metaImage);
    }
@endphp

{{-- Canonical + Description --}}
<link rel="canonical" href="{{ $metaUrl }}">
@if($metaDescription)
    <meta name="description" content="{{ Str::limit(strip_tags($metaDescription), 160) }}">
@endif

{{-- Open Graph --}}
<meta property="og:title" content="{{ $metaTitle }}">
@if($metaDescription)
    <meta property="og:description" content="{{ Str::limit(strip_tags($metaDescription), 160) }}">
@endif
@if($metaImage)
    <meta property="og:image" content="{{ $metaImage }}">
@endif
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:type" content="{{ $metaType }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $metaImage ? 'summary_large_image' : 'summary' }}">

{{-- JSON-LD: BlogPosting --}}
@if($article)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $article->meta_title ?? $article->title,
        'description' => $article->meta_description ?? $article->excerpt,
        'datePublished' => $article->published_at?->toIso8601String(),
        'dateModified' => $article->updated_at->toIso8601String(),
        'url' => route('articles.show', $article->slug),
        'image' => $metaImage,
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
            'url' => url('/about'),
        ],
        'publisher' => [
            '@type' => 'Person',
            'name' => $authorName,
            'url' => $siteUrl,
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif

{{-- JSON-LD: ImageObject --}}
@if($photo)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ImageObject',
        'name' => $photo->alt_text,
        'contentUrl' => $photo->image_url,
        'datePublished' => $photo->published_at?->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
            'url' => url('/about'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif

@props([
    'article',
    'articleUrl',
    'titleClass' => 'text-lg',
    'clampClass' => 'line-clamp-3',
    'actionsMargin' => 'mt-auto',
    'authorName' => '',
])

{{-- Category + Draft Badge --}}
<div class="flex flex-wrap gap-2">
    @if($article->category)
        <a href="{{ $article->category->urlFor('articles') }}"
           class="badge badge-primary badge-sm">
            {{ $article->category->name }}
        </a>
    @endif
    @auth
        @if($article->status === \App\Enums\Status::Private)
            <span class="badge badge-warning badge-sm">Private</span>
        @endif
    @endauth
</div>

{{-- Title (p-name) --}}
<h2 class="p-name card-title {{ $titleClass }}">
    <a href="{{ $articleUrl }}" class="u-url hover:link-primary">
        {{ $article->title }}
    </a>
</h2>

{{-- Meta --}}
<div class="flex items-center gap-4 text-sm text-base-content/60">
    <time class="dt-published" datetime="{{ $article->published_at?->toIso8601String() }}">
        {{ $article->published_at?->format('F j, Y') }}
    </time>
    <span class="flex items-center gap-1">
        <i class="ph ph-clock"></i>
        {{ $article->reading_time }} min read
    </span>
</div>

{{-- Summary (p-summary) --}}
@if($article->excerpt)
    <p class="p-summary text-base-content/80 leading-loose {{ $clampClass }}">
        {{ $article->excerpt }}
    </p>
@endif

{{-- Card Actions --}}
<div class="card-actions justify-end {{ $actionsMargin }} items-center">
    @auth
        <a href="{{ route('admin.articles.edit', $article) }}"
           class="btn btn-ghost btn-xs gap-1">
            <i class="ph ph-pencil-simple"></i>
            Edit
        </a>
    @endauth
    @if($article->isPublished())
        <a href="{{ $articleUrl }}"
           class="btn btn-primary btn-sm">
            Read More
            <i class="ph ph-arrow-right"></i>
        </a>
    @endif
</div>

{{-- Hidden author info for h-entry --}}
<span class="p-author h-card hidden">
    <span class="p-name">{{ $authorName }}</span>
</span>

<article class="h-entry relative group">
    <a href="{{ $photoUrl }}" class="block aspect-square overflow-hidden">
        <img src="{{ $imageUrl }}"
             alt="{{ $photo->alt_text }}"
             class="u-photo w-full h-full object-cover group-hover:brightness-75 transition-all duration-200">
    </a>

    @if($showAuthOverlays)
        @auth
            @if($isDraft)
                <span class="absolute top-2 left-2 badge badge-warning badge-sm">Draft</span>
            @endif
            <a href="{{ route('admin.photos.edit', $photo) }}"
               class="absolute top-2 right-2 btn btn-circle btn-xs btn-ghost bg-base-100/80 opacity-0 group-hover:opacity-100 transition-opacity"
               title="Edit photo">
                <i class="ph ph-pencil-simple text-sm"></i>
            </a>
        @endauth
    @endif

    <span class="hidden">
        <span class="p-name">{{ $photo->alt_text }}</span>
        <time class="dt-published" datetime="{{ $photo->published_at?->toIso8601String() }}">{{ $photo->published_at?->format('F j, Y') }}</time>
        <a class="u-url" href="{{ $photoUrl }}">Permalink</a>
        <span class="p-author h-card"><span class="p-name">{{ $authorName }}</span></span>
    </span>
</article>

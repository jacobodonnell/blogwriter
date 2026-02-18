@props([
    'feedUrl',
    'children',
    'articles',
    'photos',
    'currentType',
    'articleCount',
    'photoCount',
    'searchPlaceholder' => 'Search content...',
    'childrenLabel' => 'Subcategories',
])

{{-- Collapsible Children Nav --}}
@if($children->isNotEmpty())
    <div x-data="{ open: false }" class="mb-6">
        <button @click="open = !open" class="btn btn-ghost btn-sm gap-1" type="button">
            <i class="ph ph-folders text-lg"></i>
            {{ $childrenLabel }} ({{ $children->count() }})
            <i class="ph ph-caret-down text-sm transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-collapse x-cloak class="mt-3">
            <div class="flex flex-wrap gap-2">
                @foreach($children as $child)
                    <a href="{{ $child->permalink() }}" x-target.push="category-content" class="btn btn-sm btn-outline gap-1">
                        <i class="ph ph-folder text-sm"></i>
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- Filter Banner --}}
<x-filter-banner :action="$feedUrl" target="category-content" :clearRoute="$feedUrl">
    <div class="@auth sm:col-span-2 @else sm:col-span-3 @endauth">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="{{ $searchPlaceholder }}"
               class="input input-bordered w-full"
               @input.debounce.400ms="$el.form.requestSubmit()">
    </div>
    @auth
        <div class="sm:col-span-1">
            <select name="status" class="select select-bordered w-full"
                    @change="$el.form.requestSubmit()">
                <option value="">All Status</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
        </div>
    @endauth
    @if(request('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
    @endif
</x-filter-banner>

{{-- Content-Type Filter Tabs --}}
<nav class="flex gap-1 mb-6" aria-label="Content filter">
    @php
        $tabParams = array_filter([
            'search' => request('search'),
            'status' => request('status'),
        ]);
    @endphp
    <a href="{{ $feedUrl . ($tabParams ? '?' . http_build_query($tabParams) : '') }}"
       x-target.push="category-content"
       class="btn btn-sm {{ $currentType === 'all' ? 'btn-primary' : 'btn-ghost' }}">
        All
    </a>
    <a href="{{ $feedUrl . '?' . http_build_query(array_merge($tabParams, ['type' => 'articles'])) }}"
       x-target.push="category-content"
       class="btn btn-sm {{ $currentType === 'articles' ? 'btn-primary' : 'btn-ghost' }}">
        <i class="ph ph-article"></i> Articles
    </a>
    <a href="{{ $feedUrl . '?' . http_build_query(array_merge($tabParams, ['type' => 'photos'])) }}"
       x-target.push="category-content"
       class="btn btn-sm {{ $currentType === 'photos' ? 'btn-primary' : 'btn-ghost' }}">
        <i class="ph ph-camera"></i> Photos
    </a>
</nav>

{{-- Articles List --}}
@if($articles->isNotEmpty())
    <div class="space-y-8">
        @foreach($articles as $article)
            <article class="h-entry card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="p-name card-title text-xl">
                        <a href="{{ route('articles.show', $article->slug) }}" class="u-url hover:link-primary">
                            {{ $article->title }}
                        </a>
                        @auth
                            @if($article->status === \App\Enums\Status::Draft)
                                <span class="badge badge-warning badge-sm">Draft</span>
                            @endif
                        @endauth
                    </h2>

                    <div class="flex items-center gap-4 text-sm text-base-content/60 mb-3">
                        <time class="dt-published" datetime="{{ $article->published_at?->toIso8601String() }}">
                            {{ $article->published_at?->format('F j, Y') }}
                        </time>
                        <span class="flex items-center gap-1">
                            <i class="ph ph-clock"></i>
                            {{ $article->reading_time }} min read
                        </span>
                    </div>

                    <p class="p-summary text-base-content/80 leading-relaxed">
                        {{ $article->excerpt }}
                    </p>

                    <div class="card-actions justify-end mt-4">
                        <a href="{{ route('articles.show', $article->slug) }}"
                           class="btn btn-primary btn-sm gap-1">
                            Read Article
                            <i class="ph ph-arrow-right"></i>
                        </a>
                    </div>

                    <span class="p-author h-card hidden">
                        <span class="p-name">{{ $authorName }}</span>
                    </span>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $articles->links() }}
    </div>
@endif

{{-- Photos Grid --}}
@if($photos->isNotEmpty())
    <div class="{{ $articles->isNotEmpty() ? 'mt-12' : '' }}">
        <h2 class="text-2xl font-bold mb-6">
            <i class="ph ph-camera text-primary mr-2"></i>
            Photos
        </h2>

        <div class="grid grid-cols-3 md:grid-cols-4 gap-1">
            @foreach($photos as $photo)
                <article class="h-entry relative group">
                    <a href="{{ route('photos.show', $photo->slug) }}"
                       class="block aspect-square overflow-hidden">
                        <img src="{{ $photo->image_url }}"
                             alt="{{ $photo->alt_text }}"
                             class="u-photo w-full h-full object-cover group-hover:brightness-75 transition-all duration-200">
                    </a>

                    @auth
                        @if($photo->status === \App\Enums\Status::Draft)
                            <span class="absolute top-2 left-2 badge badge-warning badge-sm">Draft</span>
                        @endif
                    @endauth

                    <span class="hidden">
                        <span class="p-name">{{ $photo->alt_text }}</span>
                        <time class="dt-published" datetime="{{ $photo->published_at?->toIso8601String() }}">{{ $photo->published_at?->format('F j, Y') }}</time>
                        <a class="u-url" href="{{ route('photos.show', $photo->slug) }}">Permalink</a>
                        <span class="p-author h-card"><span class="p-name">{{ $authorName }}</span></span>
                    </span>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $photos->links() }}
        </div>
    </div>
@endif

{{-- Empty State --}}
@if($articles->isEmpty() && $photos->isEmpty())
    <div class="text-center py-16 bg-base-100 rounded-lg border border-base-200">
        <div class="text-6xl mb-4"><i class="ph ph-folder-dashed text-base-content/30"></i></div>
        @if(request('search') || request('status') || request('type'))
            <h2 class="text-xl font-bold mb-2">No content found</h2>
            <p class="text-base-content/60 mb-6">Try adjusting your filters.</p>
            <a href="{{ $feedUrl }}" x-target.push="category-content" class="btn btn-ghost">Clear Filters</a>
        @else
            <h2 class="text-xl font-bold mb-2">No content yet</h2>
            <p class="text-base-content/60 mb-6">No articles or photos to show here yet.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="ph ph-house"></i>
                Back to Home
            </a>
        @endif
    </div>
@endif

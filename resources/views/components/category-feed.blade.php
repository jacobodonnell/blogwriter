@props([
    'feedUrl',
    'categories',
    'children',
    'articles',
    'photos',
    'currentType',
    'articleCount',
    'photoCount',
    'searchPlaceholder' => 'Search content...',
    'childrenLabel' => 'Subcategories',
    'parentUrl' => null,
    'parentLabel' => null,
    'category' => null,
])

<x-filter-banner :action="$feedUrl" target="category-content" :clearRoute="$feedUrl" persistKey="categories_filters_open">
    <x-slot:toolbar>
        @auth
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                <i class="ph ph-gear text-lg"></i>
                Manage Categories
            </a>
        @endauth
    </x-slot:toolbar>

    <x-slot:navigation>
        @if($children->isNotEmpty() || $parentUrl)
            <div class="flex flex-wrap gap-2">
                @if($parentUrl)
                    <a href="{{ $parentUrl }}" x-target.push="category-content" class="btn btn-sm btn-primary gap-1">
                        <i class="ph ph-arrow-up text-sm"></i>
                        {{ $parentLabel }}
                    </a>
                @else
                    <span class="btn btn-sm btn-disabled btn-neutral gap-1">
                        <i class="ph ph-house-simple text-sm"></i>
                        Top Level
                    </span>
                @endif
                @foreach($children as $child)
                    <a href="{{ $child->permalink() }}" x-target.push="category-content" class="btn btn-sm btn-outline gap-1">
                        <i class="ph ph-folder text-sm"></i>
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </x-slot:navigation>

    <x-filters.search :placeholder="$searchPlaceholder" :colspan="auth()->check() ? 1 : 2" />
    <x-filters.category-select :categories="$categories" />
    <x-filters.select name="status" label="Status"
        :options="['published' => 'Published', 'draft' => 'Draft']"
        emptyLabel="All Status" :auth="true" />
    <x-filters.sort />

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
            'category' => request('category'),
            'sort' => request('sort'),
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
        @if(request('search') || request('status') || request('type') || request('category') || request('sort'))
            <h2 class="text-xl font-bold mb-2">No content found</h2>
            <p class="text-base-content/60 mb-6">Try adjusting your filters.</p>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <a href="{{ $feedUrl }}" x-target.push="category-content" class="btn btn-ghost">Clear Filters</a>
                @auth
                    @if($currentType !== 'photos')
                        <a href="{{ route('admin.articles.create', ['category_id' => $category?->id]) }}" class="btn btn-primary gap-1">
                            <i class="ph ph-plus"></i>
                            Write Article
                        </a>
                    @endif
                    @if($currentType !== 'articles')
                        <a href="{{ route('admin.photos.create', ['category_id' => $category?->id]) }}" class="btn {{ $currentType === 'photos' ? 'btn-primary' : 'btn-ghost' }} gap-1">
                            <i class="ph ph-upload"></i>
                            Upload Photo
                        </a>
                    @endif
                @endauth
            </div>
        @else
            <h2 class="text-xl font-bold mb-2">No content yet</h2>
            <p class="text-base-content/60 mb-6">No articles or photos to show here yet.</p>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <a href="{{ route('home') }}" class="btn @auth btn-ghost @else btn-primary @endauth">
                    <i class="ph ph-house"></i>
                    Back to Home
                </a>
                @auth
                    <a href="{{ route('admin.articles.create', ['category_id' => $category?->id]) }}" class="btn btn-primary gap-1">
                        <i class="ph ph-plus"></i>
                        Write Article
                    </a>
                    <a href="{{ route('admin.photos.create', ['category_id' => $category?->id]) }}" class="btn btn-ghost gap-1">
                        <i class="ph ph-upload"></i>
                        Upload Photo
                    </a>
                @endauth
            </div>
        @endif
    </div>
@endif

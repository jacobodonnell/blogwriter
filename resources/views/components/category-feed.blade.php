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
    'parentUrl' => null,
    'parentLabel' => null,
    'category' => null,
])

<x-filter-banner :action="$feedUrl" target="category-content" :clearRoute="$feedUrl" persistKey="categories_filters_open">
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

    <x-filters.search :placeholder="$searchPlaceholder" :colspan="4" />
    <x-filters.category-select :categories="$categories" />
    <x-filters.select name="type" label="Content Type"
        :options="['articles' => 'Articles', 'photos' => 'Photos']"
        emptyLabel="All Content" />
    <x-filters.sort />
    <x-filters.select name="status" label="Status"
        :options="['published' => 'Published', 'draft' => 'Draft']"
        emptyLabel="All Status" :auth="true" />
</x-filter-banner>

{{-- Articles List --}}
@if($articles->isNotEmpty())
    <div class="flex flex-col gap-6">
        @foreach($articles as $article)
            <x-article-card :article="$article"
                :odd="$loop->odd" :author-name="$authorName" />
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

@php
    $isRoot = is_null($category);
    $feedUrl = $isRoot
        ? route('admin.categories.explore')
        : $category->adminExploreUrl();
    $parentUrl = match (true) {
        $isRoot => null,
        $category->parent !== null => $category->parent->adminExploreUrl(),
        default => route('admin.categories.explore'),
    };
    $parentLabel = match (true) {
        $isRoot => null,
        $category->parent !== null => $category->parent->name,
        default => 'All Categories',
    };
@endphp

<div id="explore-content" x-merge="morph">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-2">
        <div>
            <h1 class="text-3xl font-bold">
                <i class="ph ph-{{ $category ? 'folder' : 'folders' }} text-primary mr-2"></i>
                {{ $categoryPath ?? 'Explore Categories' }}
            </h1>

            @if($category?->description)
                <p class="text-base-content/70 text-lg max-w-2xl mt-2">{{ $category->description }}</p>
            @elseif($isRoot)
                <p class="text-base-content/70 text-lg max-w-2xl mt-2">Browse all content by category.</p>
            @endif

            <p class="text-sm text-base-content/60 mt-2">
                {{ $articleCount }} {{ Str::plural('article', $articleCount) }}
                @if($photoCount > 0)
                    &middot; {{ $photoCount }} {{ Str::plural('photo', $photoCount) }}
                @endif
            </p>
        </div>

        <a href="{{ route('admin.categories.index') }}" class="btn btn-primary btn-sm gap-1">
            <i class="ph ph-plus"></i>
            New Category
        </a>
    </div>

    <x-filter-banner :action="$feedUrl" target="explore-content" :clearRoute="$feedUrl" persistKey="admin_explore_filters_open">
        <x-slot:navigation>
            @if($children->isNotEmpty() || $parentUrl)
                <div class="flex flex-wrap gap-2">
                    @if($parentUrl)
                        <a href="{{ $parentUrl }}" x-target.push="explore-content" class="btn btn-sm btn-primary gap-1">
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
                        <a href="{{ $child->adminExploreUrl() }}" x-target.push="explore-content" class="btn btn-sm btn-outline gap-1">
                            <i class="ph ph-folder text-sm"></i>
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </x-slot:navigation>

        <x-filter-banner.search :placeholder="$isRoot ? 'Search all content...' : 'Search in ' . $category->name . '...'" :colspan="4" />
        <x-filter-banner.select name="type" label="Content Type"
            :options="['articles' => 'Articles', 'photos' => 'Photos']"
            emptyLabel="All Content" />
        <x-filter-banner.sort />
        <x-filter-banner.select name="status" label="Status"
            :options="['published' => 'Published', 'draft' => 'Draft']"
            emptyLabel="All Status" />
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

                        @if($photo->status === \App\Enums\Status::Draft)
                            <span class="absolute top-2 left-2 badge badge-warning badge-sm">Draft</span>
                        @endif

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
                    <a href="{{ $feedUrl }}" x-target.push="explore-content" class="btn btn-ghost">Clear Filters</a>
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
                </div>
            @else
                <h2 class="text-xl font-bold mb-2">No content yet</h2>
                <p class="text-base-content/60 mb-6">No articles or photos to show here yet.</p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ route('admin.articles.create', ['category_id' => $category?->id]) }}" class="btn btn-primary gap-1">
                        <i class="ph ph-plus"></i>
                        Write Article
                    </a>
                    <a href="{{ route('admin.photos.create', ['category_id' => $category?->id]) }}" class="btn btn-ghost gap-1">
                        <i class="ph ph-upload"></i>
                        Upload Photo
                    </a>
                </div>
            @endif
        </div>
    @endif

</div>

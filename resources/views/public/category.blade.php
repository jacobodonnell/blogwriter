<x-layouts.public :title="$category->name . ' - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :title="$category->name . ' - ' . config('app.name')" :description="$category->description" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">

        {{-- Category Header --}}
        <header class="mb-8">
            <nav class="text-sm breadcrumbs mb-4">
                <ul>
                    <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                    <li><a href="{{ route('categories.index') }}" class="link link-hover">Categories</a></li>
                    @foreach($category->ancestors() as $ancestor)
                        <li><a href="{{ $ancestor->permalink() }}" class="link link-hover">{{ $ancestor->name }}</a></li>
                    @endforeach
                    <li class="text-base-content/60">{{ $category->name }}</li>
                </ul>
            </nav>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-3xl font-bold">
                    <i class="ph ph-folder text-primary mr-2"></i>
                    {{ $category->name }}
                </h1>
                @auth
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-gear text-lg"></i>
                        Manage Categories
                    </a>
                @endauth
            </div>

            @if($category->description)
                <p class="text-base-content/70 text-lg max-w-2xl mt-2">{{ $category->description }}</p>
            @endif

            <p class="text-sm text-base-content/60 mt-2">
                {{ $articleCount }} {{ Str::plural('article', $articleCount) }}
                @if($photoCount > 0)
                    &middot; {{ $photoCount }} {{ Str::plural('photo', $photoCount) }}
                @endif
            </p>

            @if($children->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach($children as $child)
                        <a href="{{ $child->permalink() }}" class="btn btn-sm btn-outline gap-1">
                            <i class="ph ph-folder text-sm"></i>
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        {{-- Content-Type Filter Tabs --}}
        <nav class="flex gap-1 mb-6" aria-label="Content filter">
            <a href="{{ $category->permalink() }}"
               class="btn btn-sm {{ $currentType === 'all' ? 'btn-primary' : 'btn-ghost' }}">
                All
            </a>
            <a href="{{ $category->permalink() }}?type=articles"
               class="btn btn-sm {{ $currentType === 'articles' ? 'btn-primary' : 'btn-ghost' }}">
                <i class="ph ph-article"></i> Articles
            </a>
            <a href="{{ $category->permalink() }}?type=photos"
               class="btn btn-sm {{ $currentType === 'photos' ? 'btn-primary' : 'btn-ghost' }}">
                <i class="ph ph-camera"></i> Photos
            </a>
        </nav>

        {{-- Articles List --}}
        @if($articles->isNotEmpty())
            <div class="space-y-8">
                @foreach($articles as $article)
                    {{-- h-entry for each article --}}
                    <article class="h-entry card bg-base-100 shadow-sm border border-base-200">
                        <div class="card-body">
                            {{-- Title (p-name) with status badge --}}
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

                            {{-- Meta --}}
                            <div class="flex items-center gap-4 text-sm text-base-content/60 mb-3">
                                <time class="dt-published" datetime="{{ $article->published_at?->toIso8601String() }}">
                                    {{ $article->published_at?->format('F j, Y') }}
                                </time>
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-clock"></i>
                                    {{ $article->reading_time }} min read
                                </span>
                            </div>

                            {{-- Summary (p-summary) --}}
                            <p class="p-summary text-base-content/80 leading-relaxed">
                                {{ $article->excerpt }}
                            </p>

                            {{-- Read More --}}
                            <div class="card-actions justify-end mt-4">
                                <a href="{{ route('articles.show', $article->slug) }}"
                                   class="btn btn-primary btn-sm gap-1">
                                    Read Article
                                    <i class="ph ph-arrow-right"></i>
                                </a>
                            </div>

                            {{-- Hidden author info for h-entry --}}
                            <span class="p-author h-card hidden">
                                <span class="p-name">{{ $authorName }}</span>
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
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

                            {{-- Auth status badge overlay --}}
                            @auth
                                @if($photo->status === \App\Enums\Status::Draft)
                                    <span class="absolute top-2 left-2 badge badge-warning badge-sm">Draft</span>
                                @endif
                            @endauth

                            {{-- Hidden microformat data --}}
                            <span class="hidden">
                                <span class="p-name">{{ $photo->alt_text }}</span>
                                <time class="dt-published" datetime="{{ $photo->published_at?->toIso8601String() }}">{{ $photo->published_at?->format('F j, Y') }}</time>
                                <a class="u-url" href="{{ route('photos.show', $photo->slug) }}">Permalink</a>
                                <span class="p-author h-card"><span class="p-name">{{ $authorName }}</span></span>
                            </span>
                        </article>
                    @endforeach
                </div>

                {{-- Photo Pagination --}}
                <div class="mt-8">
                    {{ $photos->links() }}
                </div>
            </div>
        @endif

        {{-- Empty state when no content matches filter --}}
        @if($articles->isEmpty() && $photos->isEmpty())
            <div class="text-center py-16 bg-base-100 rounded-lg border border-base-200">
                <div class="text-6xl mb-4"><i class="ph ph-folder-dashed text-base-content/30"></i></div>
                <h2 class="text-xl font-bold mb-2">No content yet</h2>
                <p class="text-base-content/60 mb-6">This category doesn't have any articles or photos yet.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="ph ph-house"></i>
                    Back to Home
                </a>
            </div>
        @endif

    </div>

</x-layouts.public>

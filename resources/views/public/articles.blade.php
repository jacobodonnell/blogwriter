<x-layouts.public title="Articles - {{ config('app.name', 'BlogWriter') }}">

    <x-slot:seo>
        <x-seo-meta title="Articles - {{ config('app.name', 'BlogWriter') }}" description="{{ setting('page_articles_subtitle', 'All articles') }}" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-5xl mx-auto">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="min-w-0">
                <h1 class="text-3xl font-bold mb-2">Articles</h1>
                @if($subtitle)
                    <p class="text-base-content/60">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="flex shrink-0 gap-2">
                <button class="btn btn-ghost btn-sm gap-1"
                        onclick="document.getElementById('article-filter-modal').showModal()">
                    <i class="ph ph-funnel text-lg"></i>
                    Filters
                    @if(request('search') || request('category') || request('status'))
                        <span class="badge badge-xs badge-primary"></span>
                    @endif
                </button>
                @auth
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-gear text-lg"></i>
                        Manage
                    </a>
                    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm gap-1">
                        <i class="ph ph-plus"></i>
                        New Article
                    </a>
                @endauth
            </div>
        </header>

        {{-- Alternating Article Cards --}}
        @if($articles->count() > 0)
            @php $placeholderUrl = placeholder_image_url(); @endphp
            <div class="flex flex-col gap-6">
                @foreach($articles as $article)
                    <article class="h-entry card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow">
                        <div class="flex flex-col {{ $loop->odd ? 'md:flex-row' : 'md:flex-row-reverse' }}">
                            {{-- Featured Image --}}
                            @php($articleImage = $article->featured_image_url ?? $placeholderUrl)
                            @if($articleImage)
                                <figure class="md:w-2/5 aspect-video md:aspect-auto overflow-hidden {{ $loop->odd ? 'md:rounded-l-2xl md:rounded-r-none' : 'md:rounded-r-2xl md:rounded-l-none' }} rounded-t-2xl md:rounded-t-none">
                                    <a href="{{ route('articles.show', $article->slug) }}">
                                        <img src="{{ $articleImage }}"
                                             alt="{{ $article->title }}"
                                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                    </a>
                                </figure>
                            @endif

                            {{-- Content --}}
                            <div class="card-body {{ $articleImage ? 'md:w-3/5' : 'w-full' }}">
                                {{-- Category --}}
                                <div class="flex flex-wrap gap-2">
                                    @if($article->category)
                                        <a href="{{ $article->category->permalink() }}"
                                           class="badge badge-primary badge-sm">
                                            {{ $article->category->name }}
                                        </a>
                                    @endif
                                    @auth
                                        @if($article->status === \App\Enums\Status::Draft)
                                            <span class="badge badge-warning badge-sm">Draft</span>
                                        @endif
                                    @endauth
                                </div>

                                {{-- Title (p-name) --}}
                                <h2 class="p-name card-title text-xl">
                                    <a href="{{ route('articles.show', $article->slug) }}" class="u-url hover:link-primary">
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
                                    <p class="p-summary text-base-content/80 leading-relaxed">
                                        {{ $article->excerpt }}
                                    </p>
                                @endif

                                {{-- Card Actions --}}
                                <div class="card-actions justify-end mt-4 items-center">
                                    @auth
                                        <a href="{{ route('admin.articles.edit', $article) }}"
                                           class="btn btn-ghost btn-xs gap-1">
                                            <i class="ph ph-pencil-simple"></i>
                                            Edit
                                        </a>
                                    @endauth
                                    <a href="{{ route('articles.show', $article->slug) }}"
                                       class="btn btn-primary btn-sm">
                                        Read More
                                        <i class="ph ph-arrow-right"></i>
                                    </a>
                                </div>

                                {{-- Hidden author info for h-entry --}}
                                <span class="p-author h-card hidden">
                                    <span class="p-name">{{ $authorName }}</span>
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="text-6xl mb-4"><i class="ph ph-note-blank text-base-content/30"></i></div>
                @if(request('search') || request('category') || request('status'))
                    <h2 class="text-2xl font-bold mb-2">No articles found</h2>
                    <p class="text-base-content/60 mb-6">Try adjusting your filters.</p>
                    <a href="{{ route('articles.index') }}" class="btn btn-ghost"
                       x-data
                       @click.prevent="
                           localStorage.removeItem('_x_articles_filter_search');
                           localStorage.removeItem('_x_articles_filter_category');
                           localStorage.removeItem('_x_articles_filter_status');
                           window.location = '{{ route('articles.index') }}';
                       ">Clear Filters</a>
                @else
                    <h2 class="text-2xl font-bold mb-2">No articles yet</h2>
                    <p class="text-base-content/60 mb-6">Check back soon for new articles.</p>
                    @auth
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                            <i class="ph ph-plus"></i>
                            Write Article
                        </a>
                    @endauth
                @endif
            </div>
        @endif
    </div>

    {{-- Filter Modal --}}
    <x-slot:filters>
        <x-filter-modal id="article-filter-modal" title="Filter Articles"
            :action="route('articles.index')" :clearRoute="route('articles.index')"
            x-data="{
                search: $persist('{{ request('search') }}').as('articles_filter_search'),
                category: $persist('{{ request('category') }}').as('articles_filter_category'),
                status: $persist('{{ request('status') }}').as('articles_filter_status'),
            }">

            {{-- Search: half width on sm+ --}}
            <div class="sm:col-span-2">
                <label class="label"><span class="label-text">Search</span></label>
                <input type="text" name="search" x-model="search"
                       placeholder="Search articles..."
                       class="input input-bordered w-full">
            </div>

            {{-- Category --}}
            <div class="@auth sm:col-span-1 @else sm:col-span-2 @endauth">
                <label class="label"><span class="label-text">Category</span></label>
                <x-category-select :categories="$categories"
                    name="category" emptyLabel="All Categories"
                    :selected="request('category')"
                    x-model="category" />
            </div>

            {{-- Status: auth only --}}
            @auth
                <div class="sm:col-span-1">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status" x-model="status" class="select select-bordered w-full">
                        <option value="">All Status</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    </select>
                </div>
            @endauth
        </x-filter-modal>
    </x-slot:filters>

</x-layouts.public>

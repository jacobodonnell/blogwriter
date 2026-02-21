<x-layouts.public :title="'Articles - ' . config('app.name', 'BlogWriter')">

    <x-slot:seo>
        <x-seo-meta :title="'Articles - ' . config('app.name', 'BlogWriter')" :description="setting('page_articles_subtitle', 'All articles')" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-5xl mx-auto">

        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[['label' => 'Articles']]" />

        {{-- Header --}}
        <header class="mb-8">
            <x-page-heading title="Articles" :subtitle="$subtitle" class="mb-2" />
        </header>

        {{-- Filter banner + Results (Alpine AJAX target) --}}
        <div id="article-results" x-merge="morph">
            <x-filter-banner :action="route('articles.index')" target="article-results" :clearRoute="route('articles.index')" persistKey="articles_filters_open">
                <x-slot:toolbar>
                    @auth
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-1">
                            <i class="ph ph-gear text-lg"></i>
                            Manage Articles
                        </a>
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm gap-1">
                            <i class="ph ph-plus"></i>
                            New Article
                        </a>
                    @endauth
                </x-slot:toolbar>

                <x-filters.search placeholder="Search by title or slug..." :colspan="auth()->check() ? 1 : 2" />
                <x-filters.category-select :categories="$categories" />
                <x-filters.select name="status" label="Status"
                    :options="['published' => 'Published', 'draft' => 'Draft']"
                    emptyLabel="All Status" :auth="true" />
                <x-filters.sort />
            </x-filter-banner>
            @if($articles->isNotEmpty())
                <div class="flex flex-col gap-6">
                    @foreach($articles as $article)
                        <x-article-card :article="$article"
                            :odd="$loop->odd" :author-name="$authorName" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $articles->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-6xl mb-4"><i class="ph ph-note-blank text-base-content/30"></i></div>
                    @if(request('search') || request('category') || request('status') || request('sort'))
                        <h2 class="text-2xl font-bold mb-2">No articles found</h2>
                        <p class="text-base-content/60 mb-6">Try adjusting your filters.</p>
                        <a href="{{ route('articles.index') }}" class="btn btn-ghost">Clear Filters</a>
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
    </div>

</x-layouts.public>

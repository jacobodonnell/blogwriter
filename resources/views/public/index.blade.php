<x-layouts.public title="Home - {{ config('app.name', 'BlogWriter') }}">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">
        
        {{-- Feed Header --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Recent Articles</h1>
            <p class="text-base-content/60">Latest thoughts on technology, life, and the absurdity of modern startup culture.</p>
        </header>

        {{-- Articles List --}}
        @if($articles->count() > 0)
            <div class="space-y-8">
                @foreach($articles as $article)
                    {{-- h-entry for each article --}}
                    <article class="h-entry card bg-base-100 shadow-sm border border-base-200">
                        <div class="card-body">
                            {{-- Categories --}}
                            @if($article->categories->count() > 0)
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach($article->categories as $category)
                                        <a href="{{ route('category.show', $category->slug) }}"
                                           class="badge badge-primary badge-sm">
                                            {{ $category->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Featured Image --}}
                            @if($article->featured_image_url)
                                <div class="aspect-video w-full overflow-hidden rounded-lg mb-4">
                                    <img src="{{ $article->featured_image_url }}"
                                         alt="{{ $article->title }}"
                                         class="w-full h-full object-cover">
                                </div>
                            @endif

                            {{-- Title (p-name) --}}
                            <h2 class="p-name card-title text-2xl">
                                <a href="{{ route('article.show', $article->slug) }}" class="u-url hover:link-primary">
                                    {{ $article->title }}
                                </a>
                            </h2>

                            {{-- Meta --}}
                            <div class="flex items-center gap-4 text-sm text-base-content/60 mb-4">
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
                                <a href="{{ route('article.show', $article->slug) }}" 
                                   class="btn btn-primary btn-sm">
                                    Read More
                                    <i class="ph ph-arrow-right"></i>
                                </a>
                            </div>

                            {{-- Hidden author info for h-entry --}}
                            <span class="p-author h-card hidden">
                                <span class="p-name">{{ \App\Models\User::first()?->name ?? 'Author' }}</span>
                            </span>
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
                <div class="text-6xl mb-4">📝</div>
                <h2 class="text-2xl font-bold mb-2">No articles yet</h2>
                <p class="text-base-content/60 mb-6">Start writing your first article in the admin panel.</p>
                <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus"></i>
                    Write Article
                </a>
            </div>
        @endif
    </div>

</x-layouts.public>

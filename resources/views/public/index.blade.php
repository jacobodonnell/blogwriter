<x-layouts.public title="Home - {{ config('app.name', 'BlogWriter') }}">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-5xl mx-auto">

        {{-- Feed Header --}}
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Recent Articles</h1>
            <p class="text-base-content/60">Latest thoughts on technology, life, and the absurdity of modern startup culture.</p>
        </header>

        {{-- Articles Bento Grid --}}
        @if($articles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $index => $article)
                    {{-- h-entry for each article --}}
                    <article class="h-entry card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow {{ $loop->first ? 'md:col-span-2 md:row-span-2' : '' }}">
                        {{-- Featured Image --}}
                        @if($article->featured_image_url)
                            <figure class="{{ $loop->first ? 'aspect-[16/10]' : 'aspect-video' }} overflow-hidden">
                                <a href="{{ route('article.show', $article->slug) }}">
                                    <img src="{{ $article->featured_image_url }}"
                                         alt="{{ $article->title }}"
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                </a>
                            </figure>
                        @endif

                        <div class="card-body">
                            {{-- Categories --}}
                            @if($article->categories->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($article->categories as $category)
                                        <a href="{{ route('category.show', $category->slug) }}"
                                           class="badge badge-primary badge-sm">
                                            {{ $category->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Title (p-name) --}}
                            <h2 class="p-name card-title {{ $loop->first ? 'text-2xl md:text-3xl' : 'text-lg' }}">
                                <a href="{{ route('article.show', $article->slug) }}" class="u-url hover:link-primary">
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

                            {{-- Summary (p-summary) — hero card gets full excerpt --}}
                            @if($loop->first || $article->excerpt)
                                <p class="p-summary text-base-content/80 leading-relaxed {{ $loop->first ? '' : 'line-clamp-2' }}">
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
                                <a href="{{ route('article.show', $article->slug) }}"
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

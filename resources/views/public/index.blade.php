<x-layouts.public title="Home - {{ config('app.name', 'BlogWriter') }}">

    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-[1fr_18rem] xl:grid-cols-[1fr_20rem] gap-8">

        {{-- Main: Articles h-feed --}}
        <div class="h-feed">

            {{-- Feed Header --}}
            <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold mb-2">Recent Articles</h1>
                    <p class="text-base-content/60">Latest thoughts on technology, life, and the absurdity of modern startup culture.</p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('articles.index') }}" class="btn btn-ghost btn-sm gap-1">
                        View All
                        <i class="ph ph-arrow-right"></i>
                    </a>
                    @auth
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm gap-1">
                            <i class="ph ph-plus"></i>
                            New Article
                        </a>
                    @endauth
                </div>
            </header>

            {{-- Articles Bento Grid --}}
            @if($articles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($articles as $index => $article)
                        {{-- h-entry for each article --}}
                        <article class="h-entry card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow {{ $loop->first ? 'md:col-span-2' : '' }}">
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

                                {{-- Summary (p-summary) --}}
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
                    @auth
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                            <i class="ph ph-plus"></i>
                            Write Article
                        </a>
                    @endauth
                </div>
            @endif
        </div>

        {{-- Sidebar: Photos --}}
        <aside>
            <div class="h-feed">
                {{-- Sidebar Header --}}
                <header class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h2 class="text-lg font-bold">Photos</h2>
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('photos.index') }}" class="btn btn-ghost btn-xs gap-1">
                            View All
                            <i class="ph ph-arrow-right text-sm"></i>
                        </a>
                        @auth
                            <a href="{{ route('admin.photos.create') }}" class="btn btn-primary btn-xs gap-1">
                                <i class="ph ph-plus text-sm"></i>
                                Upload
                            </a>
                        @endauth
                    </div>
                </header>

                {{-- 3x3 Photo Grid --}}
                @if($photos->count() > 0)
                    <div class="flex flex-col gap-1 rounded-lg overflow-hidden">
                        @foreach($photos as $photo)
                            <article class="h-entry">
                                <a href="{{ route('photos.show', $photo->slug) }}"
                                   class="block aspect-square overflow-hidden">
                                    <img src="{{ $photo->image_url }}"
                                         alt="{{ $photo->alt_text }}"
                                         class="u-photo w-full h-full object-cover hover:brightness-75 transition-all duration-200">
                                </a>
                                <span class="hidden">
                                    <span class="p-name">{{ $photo->alt_text }}</span>
                                    <time class="dt-published" datetime="{{ $photo->published_at->toIso8601String() }}">{{ $photo->published_at->format('F j, Y') }}</time>
                                    <a class="u-url" href="{{ route('photos.show', $photo->slug) }}">Permalink</a>
                                    <span class="p-author h-card"><span class="p-name">{{ $authorName }}</span></span>
                                </span>
                            </article>
                        @endforeach
                    </div>

                @else
                    <div class="text-center py-8 text-base-content/60">
                        <i class="ph ph-camera text-3xl mb-2"></i>
                        <p class="text-sm">No photos yet</p>
                    </div>
                @endif
            </div>
        </aside>

    </div>

</x-layouts.public>

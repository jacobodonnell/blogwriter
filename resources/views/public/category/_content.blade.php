<div id="category-content">

    {{-- Articles List --}}
    @if($articles !== null && $articles->count() > 0)
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
                               class="btn btn-primary btn-sm btn-ghost gap-1">
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
    @if($photos !== null && $photos->count() > 0)
        <div class="{{ $articles !== null && $articles->count() > 0 ? 'mt-12' : '' }}">
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
    @if(($articles === null || $articles->count() === 0) && ($photos === null || $photos->count() === 0))
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

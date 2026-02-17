<x-layouts.public :title="$category->name . ' - ' . config('app.name')">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">
        
        {{-- Category Header --}}
        <header class="mb-8">
            <nav class="text-sm breadcrumbs mb-4">
                <ul>
                    <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                    <li class="text-base-content/60">{{ $category->name }}</li>
                </ul>
            </nav>
            
            <h1 class="text-3xl font-bold mb-2">
                <i class="ph ph-folder text-primary mr-2"></i>
                {{ $category->name }}
            </h1>
            
            @if($category->description)
                <p class="text-base-content/70 text-lg max-w-2xl">{{ $category->description }}</p>
            @endif
            
            <p class="text-sm text-base-content/60 mt-2">
                {{ $articles->total() }} {{ Str::plural('article', $articles->total()) }} in this category
            </p>
        </header>

        {{-- Articles List --}}
        @if($articles->count() > 0)
            <div class="space-y-8">
                @foreach($articles as $article)
                    {{-- h-entry for each article --}}
                    <article class="h-entry card bg-base-100 shadow-sm border border-base-200">
                        <div class="card-body">
                            {{-- Title (p-name) --}}
                            <h2 class="p-name card-title text-xl">
                                <a href="{{ route('articles.show', $article->slug) }}" class="u-url hover:link-primary">
                                    {{ $article->title }}
                                </a>
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
            <div class="text-center py-16 bg-base-100 rounded-lg border border-base-200">
                <div class="text-6xl mb-4">📂</div>
                <h2 class="text-xl font-bold mb-2">No articles yet</h2>
                <p class="text-base-content/60 mb-6">This category doesn't have any articles yet.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="ph ph-house"></i>
                    Back to Home
                </a>
            </div>
        @endif
    </div>

</x-layouts.public>

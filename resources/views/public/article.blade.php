<x-layouts.public :title="$article->meta_title . ' - ' . config('app.name')">

    {{-- h-entry for IndieWeb --}}
    <article class="h-entry max-w-3xl mx-auto">
        
        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                @if($article->categories->count() > 0)
                    <li><a href="{{ route('category.show', $article->categories->first()->slug) }}" class="link link-hover">
                        {{ $article->categories->first()->name }}
                    </a></li>
                @endif
                <li class="text-base-content/60 truncate max-w-xs">{{ $article->title }}</li>
            </ul>
        </nav>

        {{-- Categories --}}
        @if($article->categories->count() > 0)
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($article->categories as $category)
                    <a href="{{ route('category.show', $category->slug) }}" 
                       class="badge badge-primary badge-outline">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Title (p-name) --}}
        <h1 class="p-name text-4xl md:text-5xl font-bold mb-4 leading-tight">
            {{ $article->title }}
        </h1>

        {{-- Meta Bar --}}
        <div class="flex flex-wrap items-center gap-4 text-sm text-base-content/60 mb-8 pb-8 border-b border-base-200">
            <div class="flex items-center gap-2">
                <i class="ph ph-calendar-blank"></i>
                <time class="dt-published" datetime="{{ $article->published_at?->toIso8601String() }}">
                    {{ $article->published_at?->format('F j, Y') }}
                </time>
            </div>
            <div class="flex items-center gap-2">
                <i class="ph ph-clock"></i>
                <span>{{ $article->reading_time }} min read</span>
            </div>
            @if(!empty($article->meta['meta_author']))
                <div class="flex items-center gap-2">
                    <i class="ph ph-user"></i>
                    <span class="p-author h-card">
                        <span class="p-name">{{ $article->meta['meta_author'] }}</span>
                    </span>
                </div>
            @else
                {{-- Hidden author for h-entry --}}
                <span class="p-author h-card hidden">
                    <span class="p-name">{{ \App\Models\User::first()?->name ?? 'Author' }}</span>
                </span>
            @endif
        </div>

        {{-- Featured Image --}}
        @if($article->featured_image)
            <figure class="mb-8">
                @if(\Illuminate\Support\Str::isUrl($article->featured_image))
                    <img src="{{ $article->featured_image }}" 
                         alt="{{ $article->title }}"
                         class="u-photo w-full h-auto rounded-lg shadow-md object-cover max-h-96">
                @else
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->featured_image) }}" 
                         alt="{{ $article->title }}"
                         class="u-photo w-full h-auto rounded-lg shadow-md object-cover max-h-96">
                @endif
            </figure>
        @endif

        {{-- Content (e-content) --}}
        <div class="e-content prose prose-lg max-w-none">
            {!! $article->content_html !!}
        </div>

        {{-- Footer Meta --}}
        <footer class="mt-12 pt-8 border-t border-base-200">
            {{-- Permalink --}}
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
                <i class="ph ph-link"></i>
                <span>Permalink:</span>
                <a href="{{ route('article.show', $article->slug) }}" class="u-url link link-primary">
                    {{ url()->current() }}
                </a>
            </div>

            {{-- Share/Actions --}}
            <div class="flex flex-wrap gap-3">
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}" 
                   target="_blank"
                   rel="noopener"
                   class="btn btn-sm btn-outline gap-2">
                    <i class="ph ph-twitter-logo"></i>
                    Share on Twitter
                </a>
                <button onclick="navigator.clipboard.writeText('{{ url()->current() }}'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy Link', 2000)" 
                        class="btn btn-sm btn-outline gap-2">
                    <i class="ph ph-copy"></i>
                    Copy Link
                </button>
            </div>
        </footer>

        {{-- Hidden metadata for microformats --}}
        <div class="hidden">
            <span class="dt-published">{{ $article->published_at?->toIso8601String() }}</span>
            <span class="p-name">{{ $article->title }}</span>
            <a class="u-url" href="{{ route('article.show', $article->slug) }}">Permalink</a>
        </div>
    </article>

</x-layouts.public>

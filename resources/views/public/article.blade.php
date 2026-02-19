<x-layouts.public :title="$article->meta_title . ' - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :article="$article" />
    </x-slot:seo>

    {{-- h-entry for IndieWeb --}}
    <article class="h-entry max-w-3xl mx-auto">

        {{-- Auth Toolbar --}}
        @auth
            <div class="flex items-center gap-2 mb-6">
                <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-article"></i>
                    All Articles
                </a>
                <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-pencil-simple"></i>
                    Edit
                </a>
                @if($article->status === \App\Enums\Status::Draft)
                    <span class="badge badge-warning">Draft</span>
                @endif
            </div>
        @endauth

        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[
            ['label' => 'Articles', 'url' => route('articles.index')],
            ['label' => $article->title],
        ]" class="overflow-x-auto mb-6" />

        {{-- Category --}}
        @if($article->category)
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ $article->category->permalink() }}"
                   class="badge badge-primary badge-outline">
                    {{ $article->category->name }}
                </a>
            </div>
        @endif

        {{-- Title (p-name) --}}
        <x-page-heading :title="$article->title" large class="p-name mb-4" />

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
                    <span class="p-name">{{ $authorName }}</span>
                </span>
            @endif
        </div>

        {{-- Featured Image --}}
        <x-article-featured-image :article="$article" />

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
                <a href="{{ route('articles.show', $article->slug) }}" class="u-url link link-primary">
                    {{ url()->current() }}
                </a>
            </div>

            {{-- Share/Actions --}}
            <x-share-buttons :url="url()->current()" :text="$article->title" />
        </footer>
    </article>

</x-layouts.public>

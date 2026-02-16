<x-layouts.public :title="$article->meta_title . ' - Preview - ' . config('app.name')">

    {{-- Preview Banner --}}
    <div class="bg-warning text-warning-content py-3 px-4">
        <div class="container mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="ph ph-eye text-lg"></i>
                <span class="font-medium">Preview Mode</span>
                <span class="text-sm opacity-80">— This is how your article will appear</span>
                <span @class([
                    'badge badge-sm ml-2',
                    'badge-success' => $article->isPublished(),
                    'badge-warning' => !$article->isPublished(),
                ])>
                    {{ $article->status->label() }}
                </span>
            </div>
            <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-ghost gap-1">
                <i class="ph ph-pencil-simple"></i>
                Back to Editor
            </a>
        </div>
    </div>

    {{-- Article Content (mirrors public/article.blade.php) --}}
    <article class="h-entry max-w-3xl mx-auto">

        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-6">
            <ul>
                <li><span class="text-base-content/40">Home</span></li>
                @if($article->category)
                    <li><span class="text-base-content/40">{{ $article->category->name }}</span></li>
                @endif
                <li class="text-base-content/60 truncate max-w-xs">{{ $article->title }}</li>
            </ul>
        </nav>

        {{-- Category --}}
        @if($article->category)
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="badge badge-primary badge-outline">{{ $article->category->name }}</span>
            </div>
        @endif

        {{-- Title --}}
        <h1 class="p-name text-4xl md:text-5xl font-bold mb-4 leading-tight">
            {{ $article->title }}
        </h1>

        {{-- Meta Bar --}}
        <div class="flex flex-wrap items-center gap-4 text-sm text-base-content/60 mb-8 pb-8 border-b border-base-200">
            <div class="flex items-center gap-2">
                <i class="ph ph-calendar-blank"></i>
                <time class="dt-published" datetime="{{ $article->published_at?->toIso8601String() }}">
                    {{ $article->published_at?->format('F j, Y') ?? 'Not published' }}
                </time>
            </div>
            <div class="flex items-center gap-2">
                <i class="ph ph-clock"></i>
                <span>{{ $article->reading_time }} min read</span>
            </div>
        </div>

        {{-- Featured Image --}}
        @if($article->featured_image_url)
            <figure class="mb-8">
                <img src="{{ $article->featured_image_url }}"
                     alt="{{ $article->title }}"
                     class="u-photo w-full h-auto rounded-lg shadow-md object-cover max-h-96">
            </figure>
        @endif

        {{-- Content --}}
        <div class="e-content prose prose-lg max-w-none">
            {!! $article->content_html !!}
        </div>

        {{-- Footer --}}
        <footer class="mt-12 pt-8 border-t border-base-200">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
                <i class="ph ph-link"></i>
                <span>Permalink:</span>
                <span class="text-base-content/40">{{ $article->permalink() }}</span>
            </div>
        </footer>
    </article>

</x-layouts.public>

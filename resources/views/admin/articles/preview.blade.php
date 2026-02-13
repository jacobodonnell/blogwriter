<div id="preview-panel">
    <article class="max-w-3xl mx-auto">

        {{-- Categories --}}
        @if($article->categories->count() > 0)
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($article->categories as $category)
                    <span class="badge badge-primary badge-outline">{{ $category->name }}</span>
                @endforeach
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
                <time datetime="{{ $article->published_at?->toIso8601String() }}">
                    {{ $article->published_at?->format('F j, Y') ?? 'Not published' }}
                </time>
            </div>
            <div class="flex items-center gap-2">
                <i class="ph ph-clock"></i>
                <span>{{ $article->reading_time }} min read</span>
            </div>
            <span @class([
                'badge',
                'badge-success' => $article->isPublished(),
                'badge-warning' => !$article->isPublished(),
            ])>
                {{ $article->status->label() }}
            </span>
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
    </article>
</div>

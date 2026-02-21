{{-- h-entry article card --}}
<article @class([
    'h-entry card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow',
    'md:col-span-2' => $featured,
])>
    @if($isStacked)
        {{-- Stacked layout: image on top --}}
        @if($articleImage)
            <figure class="{{ $featured ? 'aspect-[16/10]' : 'aspect-video' }} overflow-hidden">
                <a href="{{ $articleUrl }}">
                    <img src="{{ $articleImage }}"
                         alt="{{ $article->title }}"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                </a>
            </figure>
        @endif

        <div class="card-body overflow-hidden">
            <x-article-card-body
                :article="$article"
                :article-url="$articleUrl"
                :title-class="$titleClass"
                :clamp-class="$clampClass"
                actions-margin="mt-auto"
                :author-name="$authorName"
            />
        </div>
    @else
        {{-- Side-by-side layout: image left/right --}}
        <div class="flex flex-col {{ $odd ? 'md:flex-row' : 'md:flex-row-reverse' }}">
            @if($articleImage)
                <figure @class([
                    'md:w-2/5 aspect-video overflow-hidden rounded-t-2xl md:rounded-t-none',
                    'md:rounded-l-2xl md:rounded-r-none' => $odd,
                    'md:rounded-r-2xl md:rounded-l-none' => ! $odd,
                ])>
                    <a href="{{ $articleUrl }}">
                        <img src="{{ $articleImage }}"
                             alt="{{ $article->title }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </a>
                </figure>
            @endif

            <div class="card-body {{ $articleImage ? 'md:w-3/5' : 'w-full' }}">
                <x-article-card-body
                    :article="$article"
                    :article-url="$articleUrl"
                    :title-class="$titleClass"
                    :clamp-class="$clampClass"
                    actions-margin="mt-4"
                    :author-name="$authorName"
                />
            </div>
        </div>
    @endif
</article>

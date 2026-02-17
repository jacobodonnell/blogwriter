@props(['article'])

@if($article->featured_image_url)
    <figure class="mb-8">
        <img src="{{ $article->featured_image_url }}"
             alt="{{ $article->title }}"
             class="u-photo w-full h-auto rounded-lg shadow-md object-cover max-h-96">
        @if($article->featured_image_caption)
            <figcaption class="text-sm text-base-content/60 mt-2 text-center">
                {{ $article->featured_image_caption }}
            </figcaption>
        @endif
    </figure>
@endif

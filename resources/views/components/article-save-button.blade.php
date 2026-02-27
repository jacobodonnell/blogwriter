@props(['article' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>

    {{-- Main submit button --}}
    <button type="submit"
            data-test="save-button"
            @click.prevent="window.dispatchEvent(new CustomEvent('save-article'))"
            class="btn w-full gap-2"
            :class="buttonClass">
        <i class="ph" :class="buttonIcon"></i>
        <span x-text="buttonLabel" data-test="save-button-label"></span>
    </button>

    {{-- View Live (only when article is published on server) --}}
    @if($article?->exists && $article->isPublished())
        <a href="{{ route('articles.show', $article->slug) }}"
           x-show="initialStatus === 'public'"
           class="btn btn-outline w-full gap-2">
            <i class="ph ph-arrow-square-out"></i>
            View Live
        </a>
    @endif

</div>

@props(['article' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>

    {{-- Main submit button --}}
    <button type="submit"
            data-test="save-button"
            @click.prevent="
                if (buttonAction === 'publish') { document.getElementById('publish-modal').showModal(); return; }
                if (buttonAction === 'republish') { document.getElementById('republish-modal').showModal(); return; }
                if (buttonAction === 'unpublish') { document.getElementById('unpublish-modal').showModal(); return; }
                submitFullSave();
            "
            class="btn w-full gap-2"
            :class="buttonClass">
        <i class="ph" :class="buttonIcon"></i>
        <span x-text="buttonLabel" data-test="save-button-label"></span>
    </button>

    {{-- View Live (only when article is published on server) --}}
    @if($article?->exists && $article->isPublished())
        <a href="{{ route('article.show', $article->slug) }}"
           x-show="initialStatus === 'published'"
           class="btn btn-outline w-full gap-2">
            <i class="ph ph-arrow-square-out"></i>
            View Live
        </a>
    @endif

    {{-- Status hints --}}
    <p x-show="currentStatus === 'draft' && initialStatus === 'published'"
       class="text-xs text-center text-base-content/50" x-cloak>
        Currently live — unpublishing will return a 404 to visitors.
    </p>
    <p x-show="currentStatus === 'published' && initialStatus === 'draft' && !wasEverPublished"
       class="text-xs text-center text-base-content/50" x-cloak>
        This article has never been published.
    </p>
    <p x-show="currentStatus === 'published' && initialStatus === 'draft' && wasEverPublished"
       class="text-xs text-center text-base-content/50" x-cloak>
        Previously published <span x-text="originalPublishedAt"></span> — original date will be preserved.
    </p>
</div>

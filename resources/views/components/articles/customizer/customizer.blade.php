<x-layouts.customizer :title="$isNew ? 'New Article' : 'Edit: ' . $article->title" :article="$article">

    <x-slot:preview>
        <x-articles.customizer.preview-watcher :article="$article"/>
    </x-slot:preview>

    <div @photo-attached="handlePhotoAttached($event.detail)"
         x-data="articleCustomizer(@js($alpineConfig()))">

        <form x-ref="customizerForm"
              method="POST"
              enctype="multipart/form-data"
              action="{{ $isNew ? route('admin.articles.preview.store') : route('admin.articles.preview.update', $article) }}"
              x-target="preview-panel"
              @input.debounce.300ms="$el.requestSubmit()"
              novalidate>

            <x-articles.customizer.form-hidden-inputs :is-new="$isNew"/>

            <div :class="classicEditor && 'max-w-5xl mx-auto w-full overflow-hidden'">
                <div
                    :class="classicEditor ? 'grid grid-cols-1 md:grid-cols-[1fr_320px] md:gap-6 items-start md:items-stretch md:h-[calc(100vh-4rem)] md:overflow-hidden' : 'space-y-4'">

                    <div :class="classicEditor ? 'p-4 md:overflow-y-auto md:px-4 md:py-6 scroll-fade' : ''">
                        <x-articles.customizer.editor-panel :article="$article"/>
                    </div>

                    <div :class="classicEditor ? 'p-4 md:overflow-y-auto md:px-4 md:py-6 scroll-fade' : ''">
                        <x-articles.customizer.sidebar-panel
                            :article="$article"
                            :categories="$categories"
                            :photos="$photos"
                            :is-new="$isNew"
                        />
                    </div>

                </div>
            </div>

            {{-- Sticky bottom save button (normal mode only) --}}
            <template x-if="!classicEditor">
                <div
                    class="sticky bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-base-100 from-60% to-transparent pt-8">
                    <x-article-save-button :article="$article"/>
                </div>
            </template>
        </form>

        <x-articles.customizer.modals :categories="$categories"/>

    </div>

</x-layouts.customizer>

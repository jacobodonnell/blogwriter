<x-layouts.customizer :title="$isNew ? 'New Article' : 'Edit: ' . $article->title" :article="$article">

    <x-slot:preview>
        <x-articles.customizer.preview-watcher :article="$article"/>
    </x-slot:preview>

    <div @photo-attached="handlePhotoAttached($event.detail)"
         x-data="articleCustomizer(@js($alpineConfig()))"
         :class="mode === 'fullscreen' && 'grid min-h-0'">

        <form x-ref="customizerForm"
              method="POST"
              enctype="multipart/form-data"
              action="{{ $isNew ? route('admin.articles.preview.store') : route('admin.articles.preview.update', $article) }}"
              x-target="preview-panel"
              @input.debounce.300ms="$el.requestSubmit()"
              novalidate
              :class="mode === 'fullscreen' && 'grid min-h-0'">

            <x-articles.customizer.form-hidden-inputs :is-new="$isNew"/>

            <div :class="{
                'max-w-5xl mx-auto w-full overflow-hidden': mode === 'classic',
                'w-full grid min-h-0': mode === 'fullscreen',
            }">
                <div :class="{
                    'grid grid-cols-1 md:grid-cols-[1fr_320px] md:gap-6 items-start md:items-stretch md:h-[calc(100vh-4rem)] md:overflow-hidden': mode === 'classic',
                    'space-y-4': hasPreview,
                    'grid min-h-0': mode === 'fullscreen',
                }">

                    <div :class="{
                        'p-4 md:overflow-y-auto md:px-4 md:py-6 scroll-fade': mode === 'classic',
                        'grid min-h-0': mode === 'fullscreen',
                    }">
                        <x-articles.customizer.editor-panel :article="$article"/>
                    </div>

                    {{-- Sidebar panel: inline in normal/classic, slide-over in fullscreen --}}
                    <div :class="{
                        'p-4 md:overflow-y-auto md:px-4 md:py-6 scroll-fade': mode === 'classic',
                        'fixed right-0 top-0 bottom-0 w-80 max-w-[calc(100vw-3rem)] z-50 bg-base-100 border-l border-base-300 shadow-xl overflow-y-auto p-4': mode === 'fullscreen' && sidebarPanelOpen,
                        'hidden': mode === 'fullscreen' && !sidebarPanelOpen,
                    }"
                         x-show="mode !== 'fullscreen' || sidebarPanelOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full">

                        {{-- Close button for fullscreen slide-over --}}
                        <div x-show="mode === 'fullscreen'" class="flex justify-between items-center mb-4" x-cloak>
                            <span class="font-medium text-sm">Settings</span>
                            <button type="button" @click="sidebarPanelOpen = false" class="btn btn-ghost btn-xs btn-circle">
                                <i class="ph ph-x text-lg"></i>
                            </button>
                        </div>

                        <x-articles.customizer.sidebar-panel
                            :article="$article"
                            :categories="$categories"
                            :photos="$photos"
                            :is-new="$isNew"
                        />
                    </div>

                </div>
            </div>

            {{-- Sticky bottom save button (hidden in fullscreen — save via navbar or Cmd+S) --}}
            <template x-if="mode === 'split'">
                <div
                    class="sticky bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-base-100 from-60% to-transparent pt-8">
                    <x-article-save-button :article="$article"/>
                </div>
            </template>
        </form>

        {{-- Revision browser push sidebar (teleported to layout flex sibling) --}}
        <template x-teleport="#revision-panel-target">
            <div x-show="revisionPanelOpen && mode === 'fullscreen'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 class="w-80 max-w-[calc(100vw-3rem)] shrink-0 bg-base-100 border-l border-base-300 overflow-y-auto h-full"
                 x-cloak>
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-medium text-sm">Revision History</span>
                        <button type="button" @click="revisionPanelOpen = false; exitRevisionBrowsing()" class="btn btn-ghost btn-xs btn-circle">
                            <i class="ph ph-x text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-1">
                        {{-- Current version --}}
                        <button type="button"
                                @click="exitRevisionBrowsing()"
                                class="w-full text-left px-3 py-2 rounded-field text-sm transition-colors"
                                :class="!browsingRevision ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-200'">
                            <div class="font-medium">Current version</div>
                            <div class="text-xs text-base-content/50">Working draft</div>
                        </button>

                        {{-- Saved revisions --}}
                        <template x-for="(rev, index) in revisions" :key="rev.id">
                            <div>
                                <button type="button"
                                        @click="previewRevision(index)"
                                        class="w-full text-left px-3 py-2 rounded-field text-sm transition-colors"
                                        :class="browsingRevision && browsingIndex === index ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-base-200'">
                                    <div class="truncate" x-text="rev.title || 'Untitled'"></div>
                                    <div class="text-xs text-base-content/50" x-text="rev.created_at"></div>
                                </button>
                                <div x-show="browsingRevision && browsingIndex === index" x-cloak class="px-3 pb-2 space-y-1">
                                    <button type="button"
                                            @click="restoreRevision(index)"
                                            class="btn btn-primary btn-xs btn-block">
                                        Restore this version
                                    </button>
                                    <button type="button"
                                            @click="deleteRevision(index)"
                                            class="btn btn-error btn-xs btn-block">
                                        Delete revision
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="revisions.length === 0" class="text-sm text-base-content/50 px-3 py-4 text-center">
                            No revisions yet. Revisions are created each time you save.
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Backdrop for sidebar slide-over in fullscreen (intentionally not applied to revision panel — allows scrolling the editor while browsing revisions) --}}
        <div x-show="mode === 'fullscreen' && sidebarPanelOpen"
             @click="sidebarPanelOpen = false"
             class="fixed inset-0 z-40 bg-black/20"
             x-transition.opacity
             x-cloak></div>

        <x-articles.customizer.modals :categories="$categories"/>

    </div>

</x-layouts.customizer>

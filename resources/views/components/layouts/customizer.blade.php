@props(['title' => 'Customizer', 'article'])

<x-layouts.base
    :title="$title . ' - ' . config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular"
    js-entry="resources/js/app-editor.js">


    <div x-data="customizerLayout(@js(['editorMode' => setting('customizer_editor_mode', 'fullscreen')]))"
         @pointermove.window="handleDrag($event)"
         @pointerup.window="stopDrag()"
         @keydown.escape.window="if (mode === 'fullscreen') { mode = _preFullscreenMode ?? 'split'; _preFullscreenMode = null }"
         class="h-screen" :class="mode === 'fullscreen' ? 'grid grid-rows-[auto_1fr]' : 'flex flex-col'">

        {{-- Top Navbar --}}
        <header class="navbar flex-nowrap bg-base-100 border-b border-base-300 px-4 shrink-0 z-30">
            <div class="flex flex-1 items-center gap-2">
                {{-- Drawer Toggle (hidden in fullscreen — drawer is always open) --}}
                <button @click="mode = mode === 'preview' ? 'split' : 'preview'"
                        x-show="mode !== 'fullscreen'"
                        class="btn btn-ghost btn-sm btn-square tooltip tooltip-right z-10"
                        :class="{ 'btn-active': mode !== 'preview' }"
                        :data-tip="mode !== 'preview' ? 'Close editor' : 'Open editor'"
                        aria-label="Toggle editor">
                    <i class="ph ph-sidebar-simple text-lg"></i>
                </button>

                {{-- Classic editor toggle (hidden in fullscreen) --}}
                <button @click="mode = mode === 'classic' ? 'split' : 'classic'"
                        x-show="mode !== 'fullscreen'"
                        class="btn btn-ghost btn-sm btn-square tooltip tooltip-right hidden md:inline-flex"
                        :class="{ 'btn-active': mode === 'classic' }"
                        :data-tip="mode === 'classic' ? 'Exit classic editor' : 'Classic editor'"
                        aria-label="Toggle classic editor"
                        x-cloak>
                    <i class="ph text-lg" :class="mode === 'classic' ? 'ph-arrows-in-simple' : 'ph-frame-corners'"></i>
                </button>

                {{-- Fullscreen editor toggle (hidden in fullscreen) --}}
                <button @click="_preFullscreenMode = mode; mode = 'fullscreen'"
                        x-show="mode !== 'fullscreen'"
                        class="btn btn-ghost btn-sm btn-square tooltip tooltip-right inline-flex"
                        data-tip="Fullscreen editor"
                        aria-label="Fullscreen editor"
                        data-test="navbar-fullscreen"
                        x-cloak>
                    <i class="ph ph-pencil-simple-line text-lg"></i>
                </button>

                <div class="divider divider-horizontal mx-0 hidden md:flex" x-show="mode !== 'fullscreen'"></div>

                <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    <span>Articles</span>
                </a>
                <span
                    class="text-sm text-base-content/60 truncate max-w-xs hidden md:inline">{{ $article->title }}</span>
            </div>
            <div class="flex items-center gap-1 min-w-0">
                {{-- Compact save button (desktop) --}}
                <button x-show="$store.saveButton.ready"
                        @click="window.dispatchEvent(new CustomEvent('save-article'))"
                        class="btn btn-sm btn-square tooltip tooltip-bottom"
                        :class="$store.saveButton.cssClass"
                        :data-tip="$store.saveButton.label"
                        x-cloak>
                    <i class="ph text-lg" :class="$store.saveButton.icon"></i>
                </button>

                {{-- View Live (only for published articles) --}}
                @if($article->exists && $article->isPublished())
                    <a href="{{ $article->permalink() }}"
                       class="btn btn-ghost btn-sm btn-square tooltip tooltip-bottom"
                       data-tip="View Live">
                        <i class="ph ph-arrow-square-out text-lg"></i>
                    </a>
                @endif

                {{-- Draft/Live preview toggle — hidden in classic/fullscreen --}}
                <div class="hidden md:flex items-center gap-1.5 tooltip tooltip-bottom"
                     x-show="$store.saveButton.hasDraft && hasPreview"
                     x-cloak
                     :data-tip="$store.preview.mode === 'live' ? 'Switch to draft preview' : 'Switch to live preview'">
                    <span class="text-xs text-base-content/50 select-none">Live</span>
                    <input type="checkbox" class="toggle toggle-xs"
                           :checked="$store.preview.mode === 'live'"
                           @change="$store.preview.mode = $event.target.checked ? 'live' : 'draft'">
                </div>

                {{-- Viewport Presets — hidden in classic/fullscreen --}}
                <div class="join hidden md:flex" x-show="hasPreview" x-cloak>
                    <div class="tooltip tooltip-bottom" data-tip="Phone (375px)">
                        <button @click="setPreset(375)" class="btn btn-ghost btn-xs join-item"
                                :class="{ 'btn-active': previewWidth === 375 }">
                            <i class="ph ph-device-mobile text-base"></i>
                        </button>
                    </div>
                    <div class="tooltip tooltip-bottom" data-tip="Tablet (768px)">
                        <button @click="setPreset(768)" class="btn btn-ghost btn-xs join-item"
                                :class="{ 'btn-active': previewWidth === 768 }">
                            <i class="ph ph-device-tablet text-base"></i>
                        </button>
                    </div>
                    <div class="tooltip tooltip-bottom" data-tip="Desktop (1024px)">
                        <button @click="setPreset(1024)" class="btn btn-ghost btn-xs join-item"
                                :class="{ 'btn-active': previewWidth === 1024 }">
                            <i class="ph ph-desktop text-base"></i>
                        </button>
                    </div>
                    <div class="tooltip tooltip-left" data-tip="Fill available space">
                        <button @click="setPreset(0)" class="btn btn-ghost btn-xs join-item"
                                :class="{ 'btn-active': previewWidth === 0 }">
                            <i class="ph ph-arrows-out-simple text-base"></i>
                        </button>
                    </div>
                </div>

                <x-theme-toggle size="sm"/>
            </div>
        </header>

        <x-flash-messages/>

        {{-- Main Content Area --}}
        <div class="overflow-hidden relative" :class="mode === 'fullscreen' ? 'flex-1 flex min-h-0' : 'flex-1 flex'">

            {{-- Left-Edge Reopen Tab (hidden in fullscreen) --}}
            <div class="tooltip tooltip-right absolute left-0 top-1/2 -translate-y-1/2 z-20"
                 data-tip="Open editor"
                 x-show="mode === 'preview'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-cloak>
                <button @click="mode = 'split'"
                        class="bg-base-300 hover:bg-primary/20 rounded-r-lg px-1 py-6 transition-colors"
                        aria-label="Open editor">
                    <i class="ph ph-caret-right text-sm"></i>
                </button>
            </div>

            {{-- Drawer (Form Panel) --}}
            <div x-show="mode !== 'preview'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 :class="{
                     'shrink-0 overflow-y-auto md:overflow-hidden bg-base-100 w-full relative': mode === 'classic',
                     'fullscreen-drawer flex-1 min-w-0 overflow-hidden bg-base-100 relative grid min-h-0': mode === 'fullscreen',
                     'shrink-0 overflow-y-auto overflow-x-hidden bg-base-100 md:max-w-none max-w-full relative': mode === 'split',
                 }"
                 :style="{ width: mode === 'fullscreen' ? 'auto' : ((isMobile || mode === 'classic') ? '100%' : panelWidth + 'px') }"
                 x-cloak>

                {{-- Mobile close button (hidden in fullscreen) --}}
                <div class="md:hidden flex justify-between items-center p-4 pb-0" x-show="mode !== 'fullscreen'">
                    <span class="font-medium text-sm">Editor</span>
                    <button @click="mode = 'preview'" class="btn btn-ghost btn-xs btn-circle" aria-label="Close editor">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Scrollable form content with bottom padding for sticky save button --}}
                <div :class="mode === 'classic' ? '' : (mode === 'fullscreen' ? 'grid min-h-0' : 'p-4 pb-20')">
                    {{-- Validation errors toast (dispatched, inside content wrapper to avoid grid row) --}}
                    @if ($errors->any())
                        <div x-data x-init="$dispatch('toast:show', { message: '{{ addslashes($errors->first()) }}', type: 'error' })" class="hidden"></div>
                    @endif
                    {{ $slot }}
                </div>
            </div>

            {{-- Teleport target for revision panel (push sidebar in fullscreen) --}}
            <div id="revision-panel-target" x-show="mode === 'fullscreen'" class="shrink-0" x-cloak></div>

            {{-- Gutter: Right edge of drawer (hidden in fullscreen) --}}
            <div x-show="mode === 'split'"
                 class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors items-center justify-center hidden md:flex"
                 @pointerdown.prevent="startDrag('drawer', $event)"
                 x-cloak>
                <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
            </div>

            {{-- Preview Area (hidden in fullscreen) --}}
            <div class="flex-1 overflow-hidden bg-base-300 hidden md:flex items-stretch"
                 data-test="preview-panel"
                 x-show="hasPreview"
                 :class="{ '!flex': hasPreview }">

                {{-- Preview wrapper: gutter-left + preview + gutter-right --}}
                <div class="flex mx-auto items-stretch"
                     :style="{ width: previewWidth > 0 ? (previewWidth + 16) + 'px' : '100%', maxWidth: '100%' }">

                    {{-- Gutter: Left edge of preview --}}
                    <div x-show="previewWidth > 0"
                         class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors flex items-center justify-center"
                         @pointerdown.prevent="startDrag('preview-left', $event)"
                         x-cloak>
                        <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
                    </div>

                    {{-- Preview Content --}}
                    <div class="flex-1 overflow-y-auto bg-base-200 min-h-full">
                        <div class="p-6">
                            {{ $preview }}
                        </div>
                    </div>

                    {{-- Gutter: Right edge of preview --}}
                    <div x-show="previewWidth > 0"
                         class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors flex items-center justify-center"
                         @pointerdown.prevent="startDrag('preview-right', $event)"
                         x-cloak>
                        <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Floating save button (mobile, when drawer closed) --}}
        <button x-show="mode === 'preview' && $store.saveButton.ready"
                @click="window.dispatchEvent(new CustomEvent('save-article'))"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed bottom-4 right-4 z-40 btn gap-2 shadow-lg md:hidden"
                :class="$store.saveButton.cssClass"
                x-cloak>
            <i class="ph" :class="$store.saveButton.icon"></i>
            <span x-text="$store.saveButton.label"></span>
        </button>
    </div>

</x-layouts.base>

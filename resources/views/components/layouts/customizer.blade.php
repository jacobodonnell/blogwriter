@props(['title' => 'Customizer', 'article'])

<x-layouts.base
    :title="$title . ' - ' . config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular"
    js-entry="resources/js/app-editor.js">


    <div x-data="customizerLayout()"
         @pointermove.window="handleDrag($event)"
         @pointerup.window="stopDrag()"
         class="flex flex-col h-screen">

        {{-- Top Navbar --}}
        <header class="navbar flex-nowrap bg-base-100 border-b border-base-300 px-4 shrink-0 z-30">
            <div class="flex flex-1 items-center gap-2">
                {{-- Drawer Toggle (left side) --}}
                <button @click="drawerOpen ? closeDrawer() : drawerOpen = true"
                        class="btn btn-ghost btn-sm btn-square tooltip tooltip-right z-10"
                        :class="{ 'btn-active': drawerOpen }"
                        :data-tip="drawerOpen ? 'Close editor' : 'Open editor'"
                        aria-label="Toggle editor">
                    <i class="ph ph-sidebar-simple text-lg"></i>
                </button>

                <button @click="fullWidth = !fullWidth"
                        x-show="drawerOpen"
                        class="btn btn-ghost btn-sm btn-square tooltip tooltip-right hidden sm:inline-flex"
                        :class="{ 'btn-active': fullWidth }"
                        :data-tip="fullWidth ? 'Exit full width' : 'Full width editor'"
                        aria-label="Toggle full width editor"
                        x-cloak>
                    <i class="ph text-lg" :class="fullWidth ? 'ph-arrows-in-simple' : 'ph-frame-corners'"></i>
                </button>

                <div class="divider divider-horizontal mx-0 hidden sm:flex"></div>

                <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    <span class="hidden sm:inline">Articles</span>
                </a>
                <span
                    class="text-sm text-base-content/60 truncate max-w-xs hidden sm:inline">{{ $article->title }}</span>
            </div>
            <div class="flex items-center gap-1">
                {{-- Compact save button (desktop) --}}
                <button x-show="$store.saveButton.ready"
                        @click="window.dispatchEvent(new CustomEvent('save-article'))"
                        class="btn btn-sm btn-square hidden sm:inline-flex tooltip tooltip-bottom"
                        :class="$store.saveButton.cssClass"
                        :data-tip="$store.saveButton.label"
                        x-cloak>
                    <i class="ph text-lg" :class="$store.saveButton.icon"></i>
                </button>

                {{-- Viewport Presets --}}
                <div class="join hidden sm:flex" x-show="!fullWidth" x-cloak>
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

                @if($article->exists)
                    <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-arrow-square-out text-lg"></i>
                        <span class="hidden sm:inline">Preview</span>
                    </a>
                @endif
                <x-theme-toggle size="sm" />
            </div>
        </header>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success mx-4 mt-2" x-data="{ show: true }" x-show="show"
                 x-init="setTimeout(() => show = false, 3000)" x-transition>
                <i class="ph ph-check-circle text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mx-4 mt-2" x-data="{ show: true }" x-show="show"
                 x-init="setTimeout(() => show = false, 5000)" x-transition>
                <i class="ph ph-x-circle text-xl"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-hidden flex relative">

            {{-- Left-Edge Reopen Tab (visible when drawer closed) --}}
            <div class="tooltip tooltip-right absolute left-0 top-1/2 -translate-y-1/2 z-20"
                 data-tip="Open editor"
                 x-show="!drawerOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-cloak>
                <button @click="drawerOpen = true"
                        class="bg-base-300 hover:bg-primary/20 rounded-r-lg px-1 py-6 transition-colors"
                        aria-label="Open editor">
                    <i class="ph ph-caret-right text-sm"></i>
                </button>
            </div>

            {{-- Drawer (Form Panel) --}}
            <div x-show="drawerOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 class="shrink-0 overflow-y-auto bg-base-100 sm:max-w-none max-w-full relative"
                 :style="{ width: window.innerWidth < 640 || fullWidth ? '100%' : panelWidth + 'px' }"
                 x-cloak>

                {{-- Mobile close button --}}
                <div class="sm:hidden flex justify-between items-center p-4 pb-0">
                    <span class="font-medium text-sm">Editor</span>
                    <button @click="closeDrawer()" class="btn btn-ghost btn-xs btn-circle" aria-label="Close editor">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Validation errors banner (inside drawer) --}}
                @if ($errors->any())
                    <div class="px-4 pt-4" x-data="{ show: true }" x-show="show"
                         x-init="setTimeout(() => show = false, 5000)" x-transition>
                        <div role="alert" class="alert alert-error">
                            <i class="ph ph-x-circle text-xl"></i>
                            <span>Something went wrong. Please fix the errors below and try again.</span>
                        </div>
                    </div>
                @endif

                {{-- Scrollable form content with bottom padding for sticky save button --}}
                <div :class="fullWidth ? 'p-4 pb-4' : 'p-4 pb-20'">
                    {{ $slot }}
                </div>
            </div>

            {{-- Gutter: Right edge of drawer --}}
            <div x-show="drawerOpen && !fullWidth"
                 class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors items-center justify-center hidden sm:flex"
                 @pointerdown.prevent="startDrag('drawer', $event)"
                 x-cloak>
                <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
            </div>

            {{-- Preview Area --}}
            <div class="flex-1 overflow-hidden bg-base-300 hidden sm:flex items-stretch"
                 data-test="preview-panel"
                 x-show="!(fullWidth && drawerOpen)"
                 :class="{ '!flex': !drawerOpen || window.innerWidth >= 640 }">

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
        <button x-show="!drawerOpen && $store.saveButton.ready"
                @click="window.dispatchEvent(new CustomEvent('save-article'))"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed bottom-4 right-4 z-40 btn gap-2 shadow-lg sm:hidden"
                :class="$store.saveButton.cssClass"
                x-cloak>
            <i class="ph" :class="$store.saveButton.icon"></i>
            <span x-text="$store.saveButton.label"></span>
        </button>
    </div>

</x-layouts.base>

@props(['title' => 'Customizer', 'article'])

<x-layouts.base
    :title="$title . ' - ' . config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular">

    <x-slot:head>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </x-slot:head>

    <div x-data="{
            drawerOpen: JSON.parse(localStorage.getItem('customizerDrawerOpen') ?? 'true'),
            panelWidth: parseInt(localStorage.getItem('customizerWidth')) || 480,
            previewWidth: parseInt(localStorage.getItem('customizerPreviewWidth')) || 0,
            dragging: false,
            dragTarget: null,
            startX: 0,
            startWidth: 0,
            saved: false,
            init() {
                this.$watch('drawerOpen', v => localStorage.setItem('customizerDrawerOpen', JSON.stringify(v)));
                this.$watch('panelWidth', w => localStorage.setItem('customizerWidth', w));
                this.$watch('previewWidth', w => localStorage.setItem('customizerPreviewWidth', w));
            },
            get availableWidth() {
                const gutter1 = this.drawerOpen ? 8 : 0;
                const drawer = this.drawerOpen ? this.panelWidth : 0;
                const gutter2 = 8;
                return window.innerWidth - drawer - gutter1 - gutter2;
            },
            setPreset(w) {
                this.previewWidth = w;
            }
         }"
         @pointermove.window="if (dragging) {
            let delta = $event.clientX - startX;
            if (dragTarget === 'drawer') {
                panelWidth = Math.min(700, Math.max(320, startWidth + delta));
            } else if (dragTarget === 'preview') {
                previewWidth = Math.max(320, Math.min(availableWidth, startWidth + delta));
            }
         }"
         @pointerup.window="dragging = false; dragTarget = null; document.body.style.userSelect = ''; document.body.style.cursor = '';"
         class="flex flex-col h-screen">

        {{-- Top Navbar --}}
        <header class="navbar bg-base-100 border-b border-base-300 px-4 shrink-0 z-30">
            <div class="flex-1 gap-2">
                <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    <span class="hidden sm:inline">Articles</span>
                </a>
                <div class="divider divider-horizontal mx-0 hidden sm:flex"></div>
                <span class="text-sm text-base-content/60 truncate max-w-xs hidden sm:inline">{{ $article->title }}</span>
            </div>
            <div class="flex-none gap-1">
                {{-- Drawer Toggle --}}
                <button @click="drawerOpen = !drawerOpen" class="btn btn-ghost btn-sm gap-1" :class="{ 'btn-active': drawerOpen }">
                    <i class="ph ph-sidebar-simple text-lg"></i>
                    <span class="hidden sm:inline">Editor</span>
                </button>

                <div class="divider divider-horizontal mx-0 hidden sm:flex"></div>

                {{-- Viewport Presets --}}
                <div class="join hidden sm:flex">
                    <button @click="setPreset(375)" class="btn btn-ghost btn-xs join-item" :class="{ 'btn-active': previewWidth === 375 }" title="Phone (375px)">
                        <i class="ph ph-device-mobile text-base"></i>
                    </button>
                    <button @click="setPreset(768)" class="btn btn-ghost btn-xs join-item" :class="{ 'btn-active': previewWidth === 768 }" title="Tablet (768px)">
                        <i class="ph ph-device-tablet text-base"></i>
                    </button>
                    <button @click="setPreset(1024)" class="btn btn-ghost btn-xs join-item" :class="{ 'btn-active': previewWidth === 1024 }" title="Desktop (1024px)">
                        <i class="ph ph-desktop text-base"></i>
                    </button>
                    <button @click="setPreset(0)" class="btn btn-ghost btn-xs join-item" :class="{ 'btn-active': previewWidth === 0 }" title="Fill available space">
                        <i class="ph ph-arrows-out-simple text-base"></i>
                    </button>
                </div>

                <div class="divider divider-horizontal mx-0 hidden sm:flex"></div>

                {{-- Saved Indicator --}}
                <span x-show="saved" x-transition.opacity.duration.300ms class="text-success text-sm flex items-center gap-1" x-cloak>
                    <i class="ph ph-check-circle"></i> Saved
                </span>

                <a href="{{ route('admin.articles.show', $article) }}" target="_blank" class="btn btn-ghost btn-sm gap-1">
                    <i class="ph ph-arrow-square-out text-lg"></i>
                    <span class="hidden sm:inline">Preview</span>
                </a>
                <button @click="darkMode = !darkMode" class="btn btn-ghost btn-circle btn-sm" aria-label="Toggle dark mode">
                    <i x-show="!darkMode" class="ph ph-moon text-lg" x-cloak></i>
                    <i x-show="darkMode" class="ph ph-sun text-lg" x-cloak></i>
                </button>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success mx-4 mt-2" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition>
                <i class="ph ph-check-circle text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mx-4 mt-2" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                <i class="ph ph-x-circle text-xl"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-hidden flex relative">

            {{-- Left-Edge Reopen Tab (visible when drawer closed) --}}
            <button x-show="!drawerOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-full"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    @click="drawerOpen = true"
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-base-300 hover:bg-primary/20 rounded-r-lg px-1 py-6 transition-colors"
                    title="Open editor"
                    x-cloak>
                <i class="ph ph-caret-right text-sm"></i>
            </button>

            {{-- Drawer (Form Panel) --}}
            <div x-show="drawerOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 class="shrink-0 overflow-y-auto p-4 bg-base-100 sm:max-w-none max-w-full"
                 :style="{ width: window.innerWidth < 640 ? '100%' : panelWidth + 'px' }"
                 x-cloak>

                {{-- Mobile close button --}}
                <div class="sm:hidden flex justify-between items-center mb-3">
                    <span class="font-medium text-sm">Editor</span>
                    <button @click="drawerOpen = false" class="btn btn-ghost btn-xs btn-circle">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                {{ $slot }}
            </div>

            {{-- Gutter 1: Between drawer and preview --}}
            <div x-show="drawerOpen"
                 class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors items-center justify-center hidden sm:flex"
                 @pointerdown.prevent="dragging = true; dragTarget = 'drawer'; startX = $event.clientX; startWidth = panelWidth; document.body.style.userSelect = 'none'; document.body.style.cursor = 'col-resize';"
                 x-cloak>
                <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
            </div>

            {{-- Preview Area (preview + gutter2 + spacer) --}}
            <div class="flex-1 overflow-hidden bg-base-300 hidden sm:flex"
                 :class="{ '!flex': !drawerOpen || window.innerWidth >= 640 }">

                {{-- Centered Preview --}}
                <div class="flex-1 overflow-y-auto flex justify-center">
                    <div class="bg-base-200 overflow-y-auto min-h-full"
                         :style="{ width: previewWidth > 0 ? previewWidth + 'px' : '100%', maxWidth: '100%' }">
                        <div class="p-6">
                            {{ $preview }}
                        </div>
                    </div>
                </div>

                {{-- Gutter 2: Right side of preview (resize preview width) --}}
                <div x-show="previewWidth > 0"
                     class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors flex items-center justify-center"
                     @pointerdown.prevent="dragging = true; dragTarget = 'preview'; startX = $event.clientX; startWidth = previewWidth; document.body.style.userSelect = 'none'; document.body.style.cursor = 'col-resize';"
                     x-cloak>
                    <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.base>

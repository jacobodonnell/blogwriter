@props(['title' => 'Customizer', 'article'])

<x-layouts.base
    :title="$title . ' - ' . config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular">

    <x-slot:head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde@2.20.0/dist/easymde.min.css">
        <script src="https://cdn.jsdelivr.net/npm/easymde@2.20.0/dist/easymde.min.js"></script>
        <style>
            [x-cloak] { display: none !important; }

            .EasyMDEContainer .CodeMirror {
                border: 1px solid oklch(var(--bc) / 0.2);
                border-radius: var(--rounded-btn, 0.5rem);
                background: oklch(var(--b1));
                color: oklch(var(--bc));
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 0.875rem;
                min-height: 16rem;
            }
            .EasyMDEContainer .CodeMirror-focused {
                border-color: oklch(var(--p));
                outline: 2px solid oklch(var(--p) / 0.2);
            }
            .EasyMDEContainer .editor-toolbar {
                border: 1px solid oklch(var(--bc) / 0.2);
                border-bottom: none;
                border-radius: var(--rounded-btn, 0.5rem) var(--rounded-btn, 0.5rem) 0 0;
                background: oklch(var(--b2));
                padding: 4px;
            }
            .EasyMDEContainer .editor-toolbar button {
                color: oklch(var(--bc));
                width: 28px;
                height: 28px;
            }
            .EasyMDEContainer .editor-toolbar button:hover {
                background: oklch(var(--bc) / 0.1);
                border-radius: 4px;
            }
            .EasyMDEContainer .editor-toolbar button.active {
                background: oklch(var(--p) / 0.15);
            }
            .EasyMDEContainer .editor-toolbar i.separator {
                border-left-color: oklch(var(--bc) / 0.2);
            }
            .EasyMDEContainer .CodeMirror .CodeMirror-cursor {
                border-left-color: oklch(var(--bc));
            }
            .EasyMDEContainer .CodeMirror .cm-header {
                color: oklch(var(--p));
            }
            .EasyMDEContainer .CodeMirror .cm-link,
            .EasyMDEContainer .CodeMirror .cm-url {
                color: oklch(var(--a));
            }
        </style>
    </x-slot:head>

    <div x-data="{
            drawerOpen: JSON.parse(localStorage.getItem('customizerDrawerOpen') ?? 'true'),
            panelWidth: parseInt(localStorage.getItem('customizerWidth')) || 480,
            previewWidth: parseInt(localStorage.getItem('customizerPreviewWidth')) || 0,
            dragging: false,
            dragTarget: null,
            startX: 0,
            saved: false,
            init() {
                this.$watch('drawerOpen', v => localStorage.setItem('customizerDrawerOpen', JSON.stringify(v)));
                this.$watch('panelWidth', w => localStorage.setItem('customizerWidth', w));
                this.$watch('previewWidth', w => localStorage.setItem('customizerPreviewWidth', w));
            },
            get previewAreaWidth() {
                const drawer = this.drawerOpen ? this.panelWidth + 8 : 0;
                return window.innerWidth - drawer;
            },
            setPreset(w) {
                this.previewWidth = w;
            },
            startDrag(target, event) {
                this.dragging = true;
                this.dragTarget = target;
                this.startX = event.clientX;
                document.body.style.userSelect = 'none';
                document.body.style.cursor = 'col-resize';
            }
         }"
         @pointermove.window="if (dragging) {
            let delta = $event.clientX - startX;
            startX = $event.clientX;
            if (dragTarget === 'drawer') {
                panelWidth = Math.min(700, Math.max(320, panelWidth + delta));
            } else if (dragTarget === 'preview-left') {
                previewWidth = Math.max(320, Math.min(previewAreaWidth, previewWidth - delta));
            } else if (dragTarget === 'preview-right') {
                previewWidth = Math.max(320, Math.min(previewAreaWidth, previewWidth + delta));
            }
         }"
         @pointerup.window="if (dragging) { dragging = false; dragTarget = null; document.body.style.userSelect = ''; document.body.style.cursor = ''; }"
         class="flex flex-col h-screen">

        {{-- Top Navbar --}}
        <header class="navbar bg-base-100 border-b border-base-300 px-4 shrink-0 z-30">
            <div class="flex-1 gap-2">
                {{-- Drawer Toggle (left side) --}}
                <button @click="drawerOpen = !drawerOpen" class="btn btn-ghost btn-sm gap-1" :class="{ 'btn-active': drawerOpen }">
                    <i class="ph ph-sidebar-simple text-lg"></i>
                    <span class="hidden sm:inline">Editor</span>
                </button>

                <div class="divider divider-horizontal mx-0 hidden sm:flex"></div>

                <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    <span class="hidden sm:inline">Articles</span>
                </a>
                <span class="text-sm text-base-content/60 truncate max-w-xs hidden sm:inline">{{ $article->title }}</span>
            </div>
            <div class="flex-none gap-1">
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

                <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-ghost btn-sm gap-1">
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
                 class="shrink-0 overflow-y-auto bg-base-100 sm:max-w-none max-w-full relative"
                 :style="{ width: window.innerWidth < 640 ? '100%' : panelWidth + 'px' }"
                 x-cloak>

                {{-- Mobile close button --}}
                <div class="sm:hidden flex justify-between items-center p-4 pb-0">
                    <span class="font-medium text-sm">Editor</span>
                    <button @click="drawerOpen = false" class="btn btn-ghost btn-xs btn-circle">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Scrollable form content with bottom padding for sticky save button --}}
                <div class="p-4 pb-20">
                    {{ $slot }}
                </div>
            </div>

            {{-- Gutter: Right edge of drawer --}}
            <div x-show="drawerOpen"
                 class="shrink-0 w-2 bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors items-center justify-center hidden sm:flex"
                 @pointerdown.prevent="startDrag('drawer', $event)"
                 x-cloak>
                <div class="w-0.5 h-8 bg-base-content/20 rounded-full"></div>
            </div>

            {{-- Preview Area --}}
            <div class="flex-1 overflow-hidden bg-base-300 hidden sm:flex items-stretch"
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
    </div>

</x-layouts.base>

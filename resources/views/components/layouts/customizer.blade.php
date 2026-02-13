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
            dragging: false,
            panelWidth: parseInt(localStorage.getItem('customizerWidth')) || 480,
            startX: 0,
            startWidth: 0,
            init() {
                this.$watch('panelWidth', w => localStorage.setItem('customizerWidth', w));
            }
         }"
         @pointermove.window="if (dragging) {
            let delta = $event.clientX - startX;
            panelWidth = Math.min(700, Math.max(320, startWidth + delta));
         }"
         @pointerup.window="dragging = false; document.body.style.userSelect = ''; document.body.style.cursor = '';"
         class="flex flex-col h-screen">

        {{-- Top Navbar --}}
        <header class="navbar bg-base-100 border-b border-base-300 px-4 shrink-0 z-30">
            <div class="flex-1 gap-3">
                <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    Articles
                </a>
                <span class="text-sm text-base-content/60 truncate max-w-xs hidden sm:inline">{{ $article->title }}</span>
            </div>
            <div class="flex-none gap-2">
                <a href="{{ route('admin.articles.show', $article) }}" target="_blank" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-square-out text-lg"></i>
                    <span class="hidden sm:inline">Open Preview</span>
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

        {{-- Split Panel Layout --}}
        <div class="flex-1 overflow-hidden hidden lg:grid"
             :style="{ gridTemplateColumns: panelWidth + 'px 8px 1fr' }">

            {{-- Left Panel: Form --}}
            <div class="overflow-y-auto p-4">
                {{ $slot }}
            </div>

            {{-- Resizable Gutter --}}
            <div class="bg-base-300 cursor-col-resize hover:bg-primary/20 transition-colors flex items-center justify-center"
                 @pointerdown.prevent="dragging = true; startX = $event.clientX; startWidth = panelWidth; document.body.style.userSelect = 'none'; document.body.style.cursor = 'col-resize';">
                <div class="w-1 h-8 bg-base-content/20 rounded-full"></div>
            </div>

            {{-- Right Panel: Preview --}}
            <div class="overflow-y-auto bg-base-200 p-6">
                {{ $preview }}
            </div>
        </div>

        {{-- Mobile Layout: Tabs --}}
        <div class="flex-1 overflow-hidden lg:hidden flex flex-col" x-data="{ mobileTab: 'form' }">
            <div class="tabs tabs-boxed bg-base-100 border-b border-base-300 p-1 shrink-0">
                <button type="button" @click="mobileTab = 'form'" :class="{ 'tab-active': mobileTab === 'form' }" class="tab flex-1">
                    <i class="ph ph-pencil-simple mr-1"></i> Edit
                </button>
                <button type="button" @click="mobileTab = 'preview'" :class="{ 'tab-active': mobileTab === 'preview' }" class="tab flex-1">
                    <i class="ph ph-eye mr-1"></i> Preview
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4" x-show="mobileTab === 'form'" x-cloak>
                {{-- Mobile form is the same slot content --}}
                {{ $slot }}
            </div>
            <div class="flex-1 overflow-y-auto bg-base-200 p-6" x-show="mobileTab === 'preview'" x-cloak>
                {{ $preview }}
            </div>
        </div>
    </div>

</x-layouts.base>

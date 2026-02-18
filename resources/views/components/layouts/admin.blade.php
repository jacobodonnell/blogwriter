@props([
    'title' => '',
    'pageTitle' => '',
    'pageSubtitle' => '',
])

<x-layouts.base
    :title="$title ? $title . ' - ' . config('app.name', 'BlogWriter') : config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular">

    <x-slot:head>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </x-slot:head>

    <div x-data="{
            expanded: localStorage.getItem('sidebarExpanded') !== 'false',
            mobileDrawerOpen: false,
            isDesktop: window.matchMedia('(min-width: 1024px)').matches,
            tooltipText: '',
            tooltipX: 0,
            tooltipY: 0,

            toggle() {
                if (this.isDesktop) {
                    this.expanded = !this.expanded;
                } else {
                    this.mobileDrawerOpen = !this.mobileDrawerOpen;
                }
            },

            closeMobile() {
                this.mobileDrawerOpen = false;
            },

            showTooltip(event, text) {
                if (this.expanded || !this.isDesktop) return;
                const rect = event.currentTarget.getBoundingClientRect();
                this.tooltipX = rect.right + 8;
                this.tooltipY = rect.top + (rect.height / 2) - 14;
                this.tooltipText = text;
            },

            hideTooltip() {
                this.tooltipText = '';
            },

            init() {
                const mql = window.matchMedia('(min-width: 1024px)');
                mql.addEventListener('change', (e) => {
                    this.isDesktop = e.matches;
                    if (!e.matches) {
                        this.mobileDrawerOpen = false;
                    }
                });
                this.$watch('expanded', (v) => {
                    localStorage.setItem('sidebarExpanded', v);
                });
            }
        }"
        class="flex flex-col min-h-screen">

        {{-- Full-width Header --}}
        @include('components.layouts.partials.app-header')

        {{-- Body: Sidebar + Content --}}
        <div class="flex flex-1 relative">
            {{-- Sidebar --}}
            @include('components.layouts.partials.app-sidebar')

            {{-- Main Content --}}
            <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="alert alert-success m-4">
                        <i class="ph ph-check-circle text-xl"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error m-4">
                        <i class="ph ph-x-circle text-xl"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Breadcrumb --}}
                @isset($breadcrumb)
                    <div class="breadcrumbs text-sm px-4 py-2 bg-base-100 border-b border-base-300">
                        <ul>
                            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            {{ $breadcrumb }}
                        </ul>
                    </div>
                @endisset

                {{-- Main Content Area --}}
                <main class="flex-1 p-4 lg:p-6">
                    {{-- Page Title --}}
                    @if($pageTitle)
                        <div class="mb-6">
                            <h1 class="text-3xl font-bold">{{ $pageTitle }}</h1>
                            @if($pageSubtitle)
                                <p class="text-base-content/60 mt-1">{{ $pageSubtitle }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Content Slot --}}
                    <div class="max-w-7xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>

                {{-- Footer --}}
                <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-300">
                    <div>
                        <p class="text-sm">BlogWriter v{{ config('app.version') }}</p>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</x-layouts.base>

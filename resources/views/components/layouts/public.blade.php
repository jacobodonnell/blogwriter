<x-layouts.base
    :title="$title ?? config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular">

    <x-slot:head>
        <style>
            [x-cloak] { display: none !important; }
        </style>
        {{ $seo ?? '' }}
        @yield('head')
    </x-slot:head>

    @auth
        {{-- Authenticated: Unified sidebar layout --}}
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

                    {{-- Main Content --}}
                    <main class="container mx-auto px-4 py-8 flex-1">
                        {{ $slot }}
                    </main>

                    {{-- Footer --}}
                    @include('components.layouts.partials.public-footer')
                </div>
            </div>
        </div>

    @else
        {{-- Guest: Responsive navbar with mobile drawer --}}
        <div x-data="{ mobileMenuOpen: false }"
            @keydown.escape.window="mobileMenuOpen = false"
            class="flex flex-col min-h-screen">

            {{-- Header --}}
            <nav class="navbar bg-base-100 border-b border-base-200 sticky top-0 z-30">
                <div class="container mx-auto px-4 flex items-center">
                    {{-- Hamburger (mobile only) --}}
                    <div class="flex-none lg:hidden">
                        <button @click="mobileMenuOpen = true" class="btn btn-ghost btn-square" aria-label="Open menu">
                            <i class="ph ph-list text-xl"></i>
                        </button>
                    </div>

                    {{-- Logo --}}
                    <div class="flex-1">
                        <a href="{{ route('home') }}" class="btn btn-ghost text-xl font-bold">
                            {{ config('app.name', 'BlogWriter') }}
                        </a>
                    </div>

                    {{-- Desktop nav links --}}
                    <ul class="menu menu-horizontal hidden lg:flex flex-none gap-1">
                        <li><a href="{{ route('home') }}" class="{{ request()->is('/') ? 'menu-active' : '' }}">Home</a></li>
                        <li><a href="{{ route('articles.index') }}" class="{{ request()->is('articles*') && !request()->is('admin*') ? 'menu-active' : '' }}">Articles</a></li>
                        <li><a href="{{ route('photos.index') }}" class="{{ request()->is('photos') && !request()->is('admin*') ? 'menu-active' : '' }}">Photos</a></li>
                        <li><a href="{{ route('about') }}" class="{{ request()->is('about') ? 'menu-active' : '' }}">About</a></li>
                    </ul>

                    {{-- Theme Mode Cycle (always visible) --}}
                    <div class="flex-none ml-2">
                        <x-theme-toggle />
                    </div>
                </div>
            </nav>

            {{-- Mobile Drawer Backdrop --}}
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-opacity ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="fixed inset-0 bg-black/50 z-40 lg:hidden"
                 x-cloak>
            </div>

            {{-- Mobile Drawer Panel --}}
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-transform ease-out duration-200"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed top-0 left-0 h-full w-72 bg-base-100 z-50 shadow-xl lg:hidden"
                 x-cloak>

                {{-- Drawer Header --}}
                <div class="flex items-center justify-between p-4 border-b border-base-200">
                    <span class="text-lg font-bold">{{ config('app.name', 'BlogWriter') }}</span>
                    <button @click="mobileMenuOpen = false" class="btn btn-ghost btn-sm btn-square" aria-label="Close menu">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                {{-- Drawer Nav Links --}}
                <ul class="menu p-4 gap-1">
                    <li><a href="{{ route('home') }}" class="{{ request()->is('/') ? 'menu-active' : '' }}"><i class="ph ph-house text-lg"></i> Home</a></li>
                    <li><a href="{{ route('articles.index') }}" class="{{ request()->is('articles*') && !request()->is('admin*') ? 'menu-active' : '' }}"><i class="ph ph-article text-lg"></i> Articles</a></li>
                    <li><a href="{{ route('photos.index') }}" class="{{ request()->is('photos') && !request()->is('admin*') ? 'menu-active' : '' }}"><i class="ph ph-camera text-lg"></i> Photos</a></li>
                    <li><a href="{{ route('about') }}" class="{{ request()->is('about') ? 'menu-active' : '' }}"><i class="ph ph-user text-lg"></i> About</a></li>
                </ul>
            </div>

            {{-- Main Content --}}
            <main class="container mx-auto px-4 py-8 flex-1">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            @include('components.layouts.partials.public-footer')
        </div>
    @endauth

</x-layouts.base>

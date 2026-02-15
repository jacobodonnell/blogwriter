<x-layouts.base
    :title="$title ?? config('app.name', 'BlogWriter')"
    :dark-mode="auth()->check()"
    icon-weight="regular">

    <x-slot:head>
        @auth
            <style>
                [x-cloak] { display: none !important; }
            </style>
        @endauth
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
        {{-- Guest: Simple navbar --}}
        <nav class="navbar bg-base-100 border-b border-base-200">
            <div class="container mx-auto px-4">
                <div class="flex-1">
                    <a href="{{ route('home') }}" class="btn btn-ghost text-xl font-bold">
                        {{ config('app.name', 'BlogWriter') }}
                    </a>
                </div>
                <div class="flex-none gap-2">
                    <a href="{{ route('home') }}" class="btn btn-ghost">Home</a>
                    <a href="{{ route('home') }}" class="btn btn-ghost">Articles</a>
                    <a href="{{ route('photos.index') }}" class="btn btn-ghost">Photos</a>
                    <a href="{{ route('profile') }}" class="btn btn-ghost">Profile</a>

                    {{-- Dark Mode Toggle --}}
                    <button x-data="{ darkMode: false }"
                            x-init="
                                darkMode = localStorage.getItem('darkMode') === 'true';
                                if (darkMode) document.documentElement.setAttribute('data-theme', 'dark');
                            "
                            @click="
                                darkMode = !darkMode;
                                localStorage.setItem('darkMode', darkMode);
                                document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
                            "
                            class="btn btn-ghost btn-circle">
                        <i class="ph ph-moon text-xl"></i>
                    </button>
                </div>
            </div>
        </nav>

        {{-- Main Content --}}
        <main class="container mx-auto px-4 py-8 flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        @include('components.layouts.partials.public-footer')
    @endauth

</x-layouts.base>

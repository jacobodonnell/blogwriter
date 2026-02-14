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
        {{-- Authenticated: Full drawer layout (same pattern as admin) --}}
        <div class="drawer lg:drawer-open" x-data="{ drawerOpen: false }">
            <input id="drawer-toggle" type="checkbox" class="drawer-toggle" x-model="drawerOpen" />

            {{-- Drawer Content (Main Area) --}}
            <div class="drawer-content flex flex-col min-h-screen">
                {{-- Header Navbar --}}
                <header class="navbar bg-base-100 sticky top-0 z-30 shadow-sm">
                    <div class="flex-none lg:hidden">
                        <label for="drawer-toggle" class="btn btn-square btn-ghost drawer-button">
                            <i class="ph ph-list text-xl"></i>
                        </label>
                    </div>

                    <div class="flex-1">
                        <a href="{{ route('home') }}" class="btn btn-ghost text-xl font-semibold">
                            {{ config('app.name', 'BlogWriter') }}
                        </a>
                    </div>

                    <div class="flex-none gap-2">
                        {{-- Dark Mode Toggle --}}
                        <button @click="darkMode = !darkMode" class="btn btn-ghost btn-circle" aria-label="Toggle dark mode">
                            <i x-show="!darkMode" class="ph ph-moon text-xl" x-cloak></i>
                            <i x-show="darkMode" class="ph ph-sun text-xl" x-cloak></i>
                        </button>

                        {{-- User Dropdown --}}
                        <div class="dropdown dropdown-end" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="btn btn-ghost btn-circle avatar">
                                <div class="w-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-semibold">
                                    {{ auth()->user()?->name ? substr(auth()->user()->name, 0, 1) : 'U' }}
                                </div>
                            </button>
                            <ul x-show="open" x-transition x-cloak class="dropdown-content menu menu-sm z-[1] mt-3 w-52 p-2 shadow bg-base-100 rounded-box">
                                <li class="menu-title">
                                    <span>{{ auth()->user()?->name ?? 'User' }}</span>
                                    <span class="text-xs text-base-content/60">{{ auth()->user()?->email ?? '' }}</span>
                                </li>
                                <li><a href="{{ route('admin.settings') }}">Settings</a></li>
                                <div class="divider my-1"></div>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full text-left text-error">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

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

            {{-- Sidebar Drawer --}}
            <div class="drawer-side z-40">
                <label for="drawer-toggle" aria-label="close sidebar" class="drawer-overlay"></label>

                <aside class="menu bg-base-100 min-h-full w-80 p-4 lg:p-0 flex flex-col">
                    {{-- Sidebar Header (mobile) --}}
                    <div class="flex items-center justify-between mb-4 lg:mb-6 px-2 lg:hidden">
                        <span class="text-xl font-semibold">{{ config('app.name', 'BlogWriter') }}</span>
                        <label for="drawer-toggle" class="btn btn-ghost btn-sm btn-circle">
                            <i class="ph ph-x text-lg"></i>
                        </label>
                    </div>

                    {{-- Navigation Menu --}}
                    <nav class="flex-1">
                        <ul class="menu menu-lg gap-1">
                            <li>
                                <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                                    <i class="ph ph-house text-lg"></i>
                                    Home
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('home') }}" class="{{ request()->is('blog/*') && !request()->is('admin*') ? 'active' : '' }}">
                                    <i class="ph ph-article text-lg"></i>
                                    Articles
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('photos.index') }}" class="{{ request()->is('photos') && !request()->is('admin*') ? 'active' : '' }}">
                                    <i class="ph ph-image text-lg"></i>
                                    Photos
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('profile') }}" class="{{ request()->is('profile') ? 'active' : '' }}">
                                    <i class="ph ph-user text-lg"></i>
                                    Profile
                                </a>
                            </li>

                            <div class="divider my-2"></div>

                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin') ? 'active' : '' }}">
                                    <i class="ph ph-gauge text-lg"></i>
                                    Dashboard
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.articles.index') }}" class="{{ request()->is('admin/articles*') ? 'active' : '' }}">
                                    <i class="ph ph-note-pencil text-lg"></i>
                                    Manage Articles
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.photos.index') }}" class="{{ request()->is('admin/photos*') ? 'active' : '' }}">
                                    <i class="ph ph-images text-lg"></i>
                                    Manage Photos
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.categories.index') }}" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
                                    <i class="ph ph-folder text-lg"></i>
                                    Categories
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.settings') }}" class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
                                    <i class="ph ph-gear text-lg"></i>
                                    Settings
                                </a>
                            </li>
                        </ul>
                    </nav>

                    {{-- Sidebar Footer --}}
                    <div class="mt-auto pt-4 border-t border-base-300 px-2 hidden lg:block">
                        <p class="text-xs text-base-content/50">v0.1a Alpha</p>
                    </div>
                </aside>
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

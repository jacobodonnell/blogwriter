<x-layouts.base 
    :title="(isset($title) ? $title . ' - ' : '') . config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular">

    <x-slot:head>
        <!-- Drawer state for mobile -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </x-slot:head>

    <!-- Drawer Component for Mobile -->
    <div class="drawer lg:drawer-open" x-data="{ drawerOpen: false }">
        <input id="drawer-toggle" type="checkbox" class="drawer-toggle" x-model="drawerOpen" />

        <!-- Drawer Content (Main Area) -->
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Header Navbar -->
            <header class="navbar bg-base-100 sticky top-0 z-30 shadow-sm">
                <div class="flex-none lg:hidden">
                    <label for="drawer-toggle" class="btn btn-square btn-ghost drawer-button">
                        <i class="ph ph-list text-xl"></i>
                    </label>
                </div>

                <div class="flex-1">
                    <a href="/admin" class="btn btn-ghost text-xl font-semibold">
                        {{ config('app.name', 'BlogWriter') }}
                    </a>
                </div>

                <div class="flex-none gap-2">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode" class="btn btn-ghost btn-circle" aria-label="Toggle dark mode">
                        <i x-show="!darkMode" class="ph ph-moon text-xl" x-cloak></i>
                        <i x-show="darkMode" class="ph ph-sun text-xl" x-cloak></i>
                    </button>

                    <!-- User Dropdown -->
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
                            <li><a href="/admin/settings">Settings</a></li>
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

            <!-- Flash Messages -->
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

                <!-- Breadcrumb -->
            @hasSection('breadcrumb')
                <div class="breadcrumbs text-sm px-4 py-2 bg-base-100 border-b border-base-300">
                    <ul>
                        <li><a href="/admin">Dashboard</a></li>
                        @yield('breadcrumb')
                    </ul>
                </div>
            @endif

            <!-- Main Content Area -->
            <main class="flex-1 p-4 lg:p-6">
                <!-- Page Title -->
                @hasSection('page-title')
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold">@yield('page-title')</h1>
                        @hasSection('page-subtitle')
                            <p class="text-base-content/60 mt-1">@yield('page-subtitle')</p>
                        @endif
                    </div>
                @endif

                <!-- Content Slot -->
                <div class="max-w-7xl mx-auto">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-300">
                <div>
                    <p class="text-sm">{{ config('app.name', 'BlogWriter') }} v0.1a Alpha</p>
                </div>
            </footer>
        </div>

        <!-- Sidebar Drawer -->
        <div class="drawer-side z-40">
            <label for="drawer-toggle" aria-label="close sidebar" class="drawer-overlay"></label>

            <aside class="menu bg-base-100 min-h-full w-80 p-4 lg:p-0 flex flex-col">
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between mb-4 lg:mb-6 px-2 lg:hidden">
                    <span class="text-xl font-semibold">{{ config('app.name', 'BlogWriter') }}</span>
                    <label for="drawer-toggle" class="btn btn-ghost btn-sm btn-circle">
                        <i class="ph ph-x text-lg"></i>
                    </label>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1">
                    <ul class="menu menu-lg gap-1">
                        <li>
                            <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">
                                <i class="ph ph-house text-lg"></i>
                                Dashboard
                            </a>
                        </li>

                        <li>
                            <a href="/admin/articles" class="{{ request()->is('admin/articles*') ? 'active' : '' }}">
                                <i class="ph ph-article text-lg"></i>
                                Articles
                            </a>
                        </li>

                        <li>
                            <a href="/admin/categories" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
                                <i class="ph ph-folder text-lg"></i>
                                Categories
                            </a>
                        </li>

                        <li>
                            <a href="/admin/settings" class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
                                <i class="ph ph-gear text-lg"></i>
                                Settings
                            </a>
                        </li>

                        <div class="divider my-2"></div>

                        <li>
                            <a href="/" target="_blank" class="text-primary">
                                <i class="ph ph-arrow-square-out text-lg"></i>
                                View Site
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Sidebar Footer -->
                <div class="mt-auto pt-4 border-t border-base-300 px-2 hidden lg:block">
                    <p class="text-xs text-base-content/50">{{ config('app.name', 'BlogWriter') }} v0.1a</p>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.base>
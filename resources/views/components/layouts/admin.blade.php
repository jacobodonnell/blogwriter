<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: false }" x-init="
    darkMode = localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    $watch('darkMode', value => {
        localStorage.setItem('darkMode', value);
        if (value) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    });
    if (darkMode) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BlogWriter') }} - @yield('title', 'Admin')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <!-- Drawer Component for Mobile -->
    <div class="drawer lg:drawer-open" x-data="{ drawerOpen: false }">
        <input id="drawer-toggle" type="checkbox" class="drawer-toggle" x-model="drawerOpen" />

        <!-- Drawer Content (Main Area) -->
        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Header Navbar -->
            <header class="navbar bg-base-100 sticky top-0 z-30 shadow-sm">
                <div class="flex-none lg:hidden">
                    <label for="drawer-toggle" class="btn btn-square btn-ghost drawer-button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
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
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div class="dropdown dropdown-end" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-semibold">
                                {{ auth()->user()?->name ? substr(auth()->user()->name, 0, 1) : 'U' }}
                            </div>
                        </button>
                        <ul x-show="open" x-transition class="dropdown-content menu menu-sm z-[1] mt-3 w-52 p-2 shadow bg-base-100 rounded-box">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error m-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </label>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1">
                    <ul class="menu menu-lg gap-1">
                        <li>
                            <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                                Dashboard
                            </a>
                        </li>

                        <li>
                            <a href="/admin/articles" class="{{ request()->is('admin/articles*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Articles
                            </a>
                        </li>

                        <li>
                            <a href="/admin/categories" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.37l3.818-2.283c.55-.33.872-.92.872-1.56V10.85a2.25 2.25 0 00-.659-1.591l-6.581-6.581A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                                Categories
                            </a>
                        </li>

                        <li>
                            <a href="/admin/tags" class="{{ request()->is('admin/tags*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.37l3.818-2.283c.55-.33.872-.92.872-1.56V10.85a2.25 2.25 0 00-.659-1.591l-6.581-6.581A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                                Tags
                            </a>
                        </li>

                        <li>
                            <a href="/admin/settings" class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.048 4.025a3 3 0 01-4.431-1.148 2.25 2.25 0 01-2.4-2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.994 15.994 0 003.388-1.62M15.562 3.734a3 3 0 01.675 1.623 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62M12 9.75V9m0 0l-2.236.007a2.25 2.25 0 00-1.784 1.784L7.5 13.5m4.5-4.5l2.236-.007a2.25 2.25 0 011.784 1.784L16.5 13.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.192 1.298.07l.893-.267a1.125 1.125 0 011.45.567l.293.732c.177.44.07.953-.267 1.246l-.803.703a2.125 2.125 0 00-.678 1.467v.984c0 .598-.218 1.17-.678 1.467l-.803.703c-.445.39-.72.97-.72 1.594 0 .623.275 1.204.72 1.593l.803.703c.389.34.555.89.445 1.392l-.267.893a1.125 1.125 0 01-.633 1.067l-1.122.598a1.125 1.125 0 01-1.294 0l-.973-.52a1.125 1.125 0 01-1.294 0l-.973.52a1.125 1.125 0 01-1.294 0l-1.122-.598a1.125 1.125 0 01-.633-1.067l-.267-.893c-.11-.502.056-1.052.445-1.392l.803-.703c.445-.39.678-.97.678-1.467v-.984c0-.623-.233-1.203-.678-1.593l-.803-.703a1.125 1.125 0 01-.445-1.392l.267-.893a1.125 1.125 0 01.633-1.067l.973-.52c.374-.2.8-.277 1.22-.232l.892.134c.453.068.91.04 1.298-.07.396-.166.71-.506.78-.93l.149-.894z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                        </li>

                        <div class="divider my-2"></div>

                        <li>
                            <a href="/" target="_blank" class="text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
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
</body>
</html>

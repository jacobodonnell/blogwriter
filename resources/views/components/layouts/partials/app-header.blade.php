{{-- Shared header for authenticated layouts --}}
{{-- Expects Alpine.js context with: expanded, mobileDrawerOpen, isDesktop, toggle() --}}

<header class="navbar bg-base-100 sticky top-0 z-30 shadow-sm h-16">
    <div class="flex-none">
        <button @click="toggle()" class="btn btn-square btn-ghost">
            <i x-show="!mobileDrawerOpen || isDesktop" class="ph ph-sidebar-simple text-xl" x-cloak></i>
            <i x-show="mobileDrawerOpen && !isDesktop" class="ph ph-x text-xl" x-cloak></i>
        </button>
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

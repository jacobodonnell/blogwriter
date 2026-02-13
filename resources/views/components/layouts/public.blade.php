<x-layouts.base
    :title="$title ?? config('app.name', 'BlogWriter')"
    :dark-mode="false"
    icon-weight="regular">

    <x-slot:head>
        @yield('head')
    </x-slot:head>

    {{-- Public Navigation --}}
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

    {{-- Author h-card (Microformat) --}}
    <header class="bg-base-200 py-8">
        <div class="container mx-auto px-4">
            <div class="h-card flex flex-col md:flex-row items-center gap-6 max-w-3xl mx-auto">
                {{-- Author Avatar --}}
                @if(setting('profile_avatar'))
                    <img src="{{ setting('profile_avatar') }}"
                         alt="{{ setting('profile_name', \App\Models\User::first()?->name ?? 'Author') }}"
                         class="u-photo w-24 h-24 rounded-full object-cover shadow-lg">
                @endif

                <div class="text-center md:text-left">
                    {{-- Author Name --}}
                    <h1 class="p-name text-2xl font-bold mb-2">
                        {{ setting('profile_name', \App\Models\User::first()?->name ?? 'Author') }}
                    </h1>

                    {{-- Author Bio --}}
                    @if(setting('profile_bio'))
                        <p class="p-note text-base-content/70 max-w-md">
                            {{ setting('profile_bio') }}
                        </p>
                    @endif

                    {{-- Social Links (rel-me for IndieAuth) --}}
                    @if(setting('profile_github') || setting('profile_mastodon') || setting('profile_bluesky') || setting('profile_email'))
                        <div class="flex gap-3 mt-4 justify-center md:justify-start">
                            @if(setting('profile_github'))
                                <a href="{{ setting('profile_github') }}" rel="me" class="u-url btn btn-sm btn-ghost gap-1">
                                    <i class="ph ph-github-logo text-lg"></i>
                                    GitHub
                                </a>
                            @endif
                            @if(setting('profile_mastodon'))
                                <a href="{{ setting('profile_mastodon') }}" rel="me" class="u-url btn btn-sm btn-ghost gap-1">
                                    <i class="ph ph-mastodon-logo text-lg"></i>
                                    Mastodon
                                </a>
                            @endif
                            @if(setting('profile_bluesky'))
                                <a href="{{ setting('profile_bluesky') }}" rel="me" class="u-url btn btn-sm btn-ghost gap-1">
                                    <i class="ph ph-butterfly text-lg"></i>
                                    Bluesky
                                </a>
                            @endif
                            @if(setting('profile_email'))
                                <a href="mailto:{{ setting('profile_email') }}" rel="me" class="u-url btn btn-sm btn-ghost gap-1">
                                    <i class="ph ph-envelope text-lg"></i>
                                    Email
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- Hidden h-card properties for microformats --}}
                    @if(setting('profile_url'))
                        <a href="{{ setting('profile_url') }}" class="u-url hidden" rel="me">Website</a>
                    @endif
                    <a href="{{ route('home') }}" class="u-url hidden">Homepage</a>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="container mx-auto px-4 py-8 flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="footer footer-center p-6 bg-base-100 border-t border-base-200">
        <div>
            <p class="text-sm text-base-content/60">
                © {{ date('Y') }} {{ config('app.name', 'BlogWriter') }} -
                <a href="{{ route('admin.dashboard') }}" class="link link-primary">Admin</a>
            </p>
            <p class="text-xs text-base-content/40 mt-2">
                Powered by <a href="https://blogwriter.dev" class="link" target="_blank">BlogWriter</a>
            </p>
        </div>
    </footer>

</x-layouts.base>

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

    {{-- Main Content --}}
    <main class="container mx-auto px-4 py-8 flex-1">
        {{ $slot }}
    </main>

    {{-- Footer with compact h-card (Microformat) --}}
    <footer class="footer footer-center p-6 bg-base-100 border-t border-base-200">
        <div class="h-card flex flex-col items-center gap-2">
            <div class="flex items-center gap-2">
                @if(setting('profile_avatar'))
                    <img src="{{ setting('profile_avatar') }}"
                         alt="{{ setting('profile_name', \App\Models\User::first()?->name ?? 'Author') }}"
                         class="u-photo w-8 h-8 rounded-full object-cover">
                @endif
                <a href="{{ route('home') }}" rel="me" class="u-url u-uid p-name text-sm font-medium link link-hover">
                    {{ setting('profile_name', \App\Models\User::first()?->name ?? 'Author') }}
                </a>
            </div>

            @if(setting('profile_bio'))
                <span class="p-note hidden">{{ setting('profile_bio') }}</span>
            @endif

            {{-- Social rel-me links (icon-only) --}}
            @php
                $socialLinks = collect([
                    ['key' => 'profile_github', 'icon' => 'github-logo', 'label' => 'GitHub'],
                    ['key' => 'profile_mastodon', 'icon' => 'mastodon-logo', 'label' => 'Mastodon'],
                    ['key' => 'profile_bluesky', 'icon' => 'butterfly', 'label' => 'Bluesky'],
                    ['key' => 'profile_email', 'icon' => 'envelope', 'label' => 'Email'],
                ])->filter(fn ($link) => setting($link['key']));
            @endphp
            @if($socialLinks->isNotEmpty())
                <div class="flex gap-2">
                    @foreach($socialLinks as $link)
                        <a href="{{ $link['key'] === 'profile_email' ? 'mailto:' . setting($link['key']) : setting($link['key']) }}"
                           rel="me"
                           class="u-url btn btn-ghost btn-xs btn-circle"
                           title="{{ $link['label'] }}">
                            <i class="ph ph-{{ $link['icon'] }} text-base"></i>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Hidden h-card properties for microformats --}}
            @if(setting('profile_url'))
                <a href="{{ setting('profile_url') }}" class="u-url hidden" rel="me">Website</a>
            @endif

            <p class="text-sm text-base-content/60">
                © {{ date('Y') }} {{ config('app.name', 'BlogWriter') }} -
                <a href="{{ route('admin.dashboard') }}" class="link link-primary">Admin</a>
            </p>
            <p class="text-xs text-base-content/40">
                Powered by <a href="https://blogwriter.dev" class="link" target="_blank">BlogWriter</a>
            </p>
        </div>
    </footer>

</x-layouts.base>

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
                <a href="{{ route('about') }}" class="btn btn-ghost">About</a>
                
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
                <img src="https://picsum.photos/seed/author/200/200" 
                     alt="Author avatar" 
                     class="u-photo w-24 h-24 rounded-full object-cover shadow-lg">
                
                <div class="text-center md:text-left">
                    {{-- Author Name --}}
                    <h1 class="p-name text-2xl font-bold mb-2">
                        {{ \App\Models\User::first()?->name ?? 'Author Name' }}
                    </h1>
                    
                    {{-- Author Bio --}}
                    <p class="p-note text-base-content/70 max-w-md">
                        IndieWeb-native blogger. Writing about technology, life, and the absurdity of modern startup culture. Own your content.
                    </p>
                    
                    {{-- Social Links (rel-me for IndieAuth) --}}
                    <div class="flex gap-3 mt-4 justify-center md:justify-start">
                        <a href="https://github.com/username" rel="me" class="btn btn-sm btn-ghost gap-1">
                            <i class="ph ph-github-logo text-lg"></i>
                            GitHub
                        </a>
                        <a href="https://twitter.com/username" rel="me" class="btn btn-sm btn-ghost gap-1">
                            <i class="ph ph-twitter-logo text-lg"></i>
                            Twitter
                        </a>
                        <a href="mailto:email@example.com" rel="me" class="btn btn-sm btn-ghost gap-1">
                            <i class="ph ph-envelope text-lg"></i>
                            Email
                        </a>
                    </div>
                    
                    {{-- Hidden h-card properties for microformats --}}
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
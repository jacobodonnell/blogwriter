<x-layouts.public title="About - {{ config('app.name', 'BlogWriter') }}">

    <x-slot:seo>
        <x-seo-meta title="About - {{ config('app.name', 'BlogWriter') }}" description="{{ setting('profile_bio', '') }}" />
    </x-slot:seo>

    <div class="max-w-3xl mx-auto">

        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                <li class="text-base-content/60">About</li>
            </ul>
        </nav>

        {{-- Profile h-card --}}
        <div class="h-card">

            {{-- Profile Header --}}
            <header class="mb-12 flex flex-col md:flex-row items-center md:items-start gap-6">
                @if(setting('profile_avatar'))
                    <img src="{{ setting('profile_avatar') }}"
                         alt="{{ $user->name ?? 'Author' }}"
                         class="u-photo w-32 h-32 rounded-full object-cover shadow-lg">
                @endif

                <div class="text-center md:text-left">
                    <h1 class="p-name text-4xl font-bold mb-4">
                        {{ $user->name ?? 'Author' }}
                    </h1>

                    @if(setting('profile_bio'))
                        <p class="p-note text-xl text-base-content/70 leading-relaxed">
                            {{ setting('profile_bio') }}
                        </p>
                    @endif
                </div>
            </header>

            {{-- Social Links --}}
            @if(setting('profile_github') || setting('profile_mastodon') || setting('profile_bluesky') || setting('profile_email'))
                <section class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Connect</h2>
                    <div class="flex flex-wrap gap-3">
                        @if(setting('profile_github'))
                            <a href="{{ setting('profile_github') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-github-logo text-xl"></i>
                                GitHub
                            </a>
                        @endif

                        @if(setting('profile_mastodon'))
                            <a href="{{ setting('profile_mastodon') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-mastodon-logo text-xl"></i>
                                Mastodon
                            </a>
                        @endif

                        @if(setting('profile_bluesky'))
                            <a href="{{ setting('profile_bluesky') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-butterfly text-xl"></i>
                                Bluesky
                            </a>
                        @endif

                        @if(setting('profile_email'))
                            <a href="mailto:{{ setting('profile_email') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-envelope text-xl"></i>
                                Email
                            </a>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Hidden h-card properties --}}
            <a href="{{ config('app.url') }}" class="u-url hidden" rel="me">Website</a>
            <a href="{{ route('home') }}" class="u-url hidden">Homepage</a>
        </div>

    </div>

</x-layouts.public>

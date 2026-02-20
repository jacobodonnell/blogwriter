<x-layouts.public :title="'About - ' . config('app.name', 'BlogWriter')">

    <x-slot:seo>
        <x-seo-meta :title="'About - ' . config('app.name', 'BlogWriter')" :description="setting('profile_bio', '')" />
    </x-slot:seo>

    <div class="max-w-3xl mx-auto">

        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[['label' => 'About']]" class="mb-6" />

        @auth
            <div class="flex items-center gap-2 mb-6">
                <a href="{{ route('admin.settings') }}" class="btn btn-ghost btn-sm gap-1">
                    <i class="ph ph-gear text-lg"></i>
                    Edit Profile
                </a>
            </div>
        @endauth

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
                    <x-page-heading :title="$user->name ?? 'Author'" large class="p-name mb-4" />

                    @if(setting('profile_bio'))
                        <div class="p-note text-xl text-base-content/70 leading-relaxed prose prose-xl max-w-none">
                            {!! \App\Support\Markdown::render(setting('profile_bio')) !!}
                        </div>
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

<x-layouts.public title="Profile - {{ config('app.name', 'BlogWriter') }}">

    <div class="max-w-3xl mx-auto">

        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                <li class="text-base-content/60">Profile</li>
            </ul>
        </nav>

        {{-- Profile h-card --}}
        <div class="h-card">

            {{-- Profile Header --}}
            <header class="mb-12 flex flex-col md:flex-row items-center md:items-start gap-6">
                @if(setting('profile_avatar'))
                    <img src="{{ setting('profile_avatar') }}"
                         alt="{{ setting('profile_name', 'Author') }}"
                         class="u-photo w-32 h-32 rounded-full object-cover shadow-lg">
                @endif

                <div class="text-center md:text-left">
                    <h1 class="p-name text-4xl font-bold mb-4">
                        {{ setting('profile_name', $user->name ?? 'Author') }}
                    </h1>

                    @if(setting('profile_bio'))
                        <p class="p-note text-xl text-base-content/70 leading-relaxed">
                            {{ setting('profile_bio') }}
                        </p>
                    @endif
                </div>
            </header>

            {{-- Social Links --}}
            @if(setting('social_github') || setting('social_twitter') || setting('social_email'))
                <section class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Connect</h2>
                    <div class="flex flex-wrap gap-3">
                        @if(setting('social_github'))
                            <a href="{{ setting('social_github') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-github-logo text-xl"></i>
                                GitHub
                            </a>
                        @endif

                        @if(setting('social_twitter'))
                            <a href="{{ setting('social_twitter') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-twitter-logo text-xl"></i>
                                Twitter
                            </a>
                        @endif

                        @if(setting('social_email'))
                            <a href="mailto:{{ setting('social_email') }}" rel="me" class="u-url btn btn-outline gap-2">
                                <i class="ph ph-envelope text-xl"></i>
                                Email
                            </a>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Hidden h-card properties --}}
            @if(setting('social_website'))
                <a href="{{ setting('social_website') }}" class="u-url hidden" rel="me">Website</a>
            @endif
            <a href="{{ route('home') }}" class="u-url hidden">Homepage</a>
        </div>

    </div>

</x-layouts.public>

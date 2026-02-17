{{-- Footer with compact h-card (Microformat) --}}
<footer class="footer footer-center p-6 bg-base-100 border-t border-base-200">
    <div class="h-card flex flex-col items-center gap-2">
        <div class="flex items-center gap-2">
            @if(setting('profile_avatar'))
                <img src="{{ setting('profile_avatar') }}"
                     alt="{{ \App\Models\User::first()?->name ?? 'Author' }}"
                     class="u-photo w-8 h-8 rounded-full object-cover">
            @endif
            <a href="{{ route('home') }}" rel="me" class="u-url u-uid p-name text-sm font-medium link link-hover">
                {{ \App\Models\User::first()?->name ?? 'Author' }}
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
        <a href="{{ config('app.url') }}" class="u-url hidden" rel="me">Website</a>

        <p class="text-sm text-base-content/60">
            © {{ date('Y') }} {{ config('app.name', 'BlogWriter') }}
        </p>
        <p class="text-xs text-base-content/40">
            Powered by <a href="https://blogwriter.tech" class="link" target="_blank">BlogWriter</a>
        </p>
    </div>
</footer>

<x-settings-layout active="robots">
    <form method="POST" action="{{ route('admin.settings.robots.update') }}">
        @csrf
        @method('PUT')

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-robot text-xl"></i>
                    Robots.txt
                </h2>
                <p class="text-sm text-base-content/60 font-admin">Controls which pages search engines and crawlers can access.</p>

                <div class="mt-4">
                    <textarea
                        name="robots_txt"
                        rows="20"
                        class="textarea textarea-bordered w-full font-mono text-sm @error('robots_txt') textarea-error @enderror"
                    >{{ old('robots_txt', $robotsTxt) }}</textarea>

                    @error('robots_txt')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="card-actions justify-end mt-4">
                    <button type="submit" class="btn btn-primary font-admin">
                        <i class="ph ph-floppy-disk text-lg"></i>
                        Save Robots.txt
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- About robots.txt --}}
    <div class="card bg-base-100 shadow mt-6">
        <div class="card-body">
            <h2 class="card-title font-admin text-base">
                <i class="ph ph-info text-xl"></i>
                About robots.txt
            </h2>
            <div class="text-sm text-base-content/70 font-admin space-y-2">
                <p>
                    <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">robots.txt</code> is a convention
                    that asks crawlers which pages to skip — it is not enforced. Respectful bots like
                    <strong>Googlebot</strong> and <strong>Bingbot</strong> honour it reliably.
                </p>
                <p>
                    Many AI training crawlers are less consistent. <strong>Bytespider</strong> (ByteDance/TikTok)
                    is widely reported to ignore it. <strong>Perplexity</strong> has been documented cycling through
                    multiple user-agents and IP ranges to work around blocks. Even crawlers that officially support
                    <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">robots.txt</code> —
                    such as <strong>GPTBot</strong> — have been reported looping on single URLs in the wild.
                </p>
                <p>
                    Beyond non-compliance, your content may already exist in older public datasets like
                    <strong>Common Crawl</strong> that were collected before you added any directives.
                    For stronger protection, use server-level controls: IP blocking, rate limiting,
                    or a WAF/CDN rule that targets known AI crawler user-agents.
                </p>
            </div>
        </div>
    </div>
</x-settings-layout>

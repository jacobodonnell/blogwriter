<x-settings-layout active="export">
    <div class="space-y-6">

        {{-- Export Articles Card --}}
        <div class="card bg-base-100 shadow" x-data="{ exporting: false }">
            <div class="card-body">
                <h2 class="card-title font-admin">
                    <i class="ph ph-file-text text-xl"></i>
                    Articles
                </h2>
                <p class="text-sm text-base-content/60 font-admin">
                    Export all {{ $articleCount }} {{ Str::plural('article', $articleCount) }} as Markdown files with YAML frontmatter.
                    Each article is saved as <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">{slug}.md</code>.
                </p>

                <form method="POST" action="{{ route('admin.export.articles') }}" @submit="exporting = true" class="mt-4">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-primary font-admin"
                        :disabled="exporting"
                        data-test="export-articles-btn"
                    >
                        <span x-show="!exporting">
                            <i class="ph ph-download-simple text-lg"></i>
                            Export Articles
                        </span>
                        <span x-show="exporting" x-cloak class="flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span>
                            Generating ZIP&hellip;
                        </span>
                    </button>
                </form>
            </div>
        </div>

        {{-- About the export format --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title font-admin text-base">
                    <i class="ph ph-info text-xl"></i>
                    About the Export Format
                </h2>
                <div class="text-sm text-base-content/70 font-admin space-y-2">
                    <p>
                        Each article is exported as a Markdown file with YAML frontmatter — a widely-supported format
                        compatible with <strong>Hugo</strong>, <strong>Jekyll</strong>, <strong>Eleventy (11ty)</strong>,
                        and most other static site generators.
                    </p>
                    <p>
                        Frontmatter includes: <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">title</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">date</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">slug</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">draft</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">category</code>,
                        <code class="font-mono text-xs bg-base-200 px-1 py-0.5 rounded">past_slugs</code>, and SEO fields.
                    </p>
                    <p class="text-base-content/50">
                        Photos and uploaded media are not included in this export.
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-settings-layout>

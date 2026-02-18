<x-layouts.public title="Categories - {{ config('app.name') }}">

    <x-slot:seo>
        <x-seo-meta :title="'Categories - ' . config('app.name')" description="Browse all categories" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">

        {{-- Alpine AJAX morph target --}}
        <div id="category-content" x-merge="morph" data-page-title="Categories - {{ config('app.name') }}">

            {{-- Header --}}
            <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold">
                        <i class="ph ph-folders text-primary mr-2"></i>
                        Categories
                    </h1>
                    <p class="text-sm text-base-content/60 mt-2">
                        {{ $articleCount }} {{ Str::plural('article', $articleCount) }}
                        @if($photoCount > 0)
                            &middot; {{ $photoCount }} {{ Str::plural('photo', $photoCount) }}
                        @endif
                    </p>
                </div>
                @auth
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-gear text-lg"></i>
                        Manage
                    </a>
                @endauth
            </header>

            <x-category-feed
                :feedUrl="route('categories.index')"
                :children="$children"
                :articles="$articles"
                :photos="$photos"
                :currentType="$currentType"
                :articleCount="$articleCount"
                :photoCount="$photoCount"
                searchPlaceholder="Search all content..."
                childrenLabel="Categories"
            />

        </div>
    </div>

</x-layouts.public>

<x-layouts.public title="Categories - {{ config('app.name') }}">

    <x-slot:seo>
        <x-seo-meta :title="'Categories - ' . config('app.name')" description="Browse all categories" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <h1 class="text-3xl font-bold">
                <i class="ph ph-folders text-primary mr-2"></i>
                Categories
            </h1>
            @auth
                <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                    <i class="ph ph-gear text-lg"></i>
                    Manage
                </a>
            @endauth
        </header>

        {{-- Category Grid --}}
        @if($categories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categories as $category)
                    <a href="{{ $category->permalink() }}"
                       class="card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <i class="ph ph-folder text-primary"></i>
                                {{ $category->name }}
                            </h2>

                            @if($category->description)
                                <p class="text-base-content/70 text-sm line-clamp-2">{{ $category->description }}</p>
                            @endif

                            <div class="flex flex-wrap gap-3 text-sm text-base-content/60 mt-2">
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-article"></i>
                                    {{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}
                                </span>
                                @if($category->photos_count > 0)
                                    <span class="flex items-center gap-1">
                                        <i class="ph ph-camera"></i>
                                        {{ $category->photos_count }} {{ Str::plural('photo', $category->photos_count) }}
                                    </span>
                                @endif
                                @if($category->children_count > 0)
                                    <span class="flex items-center gap-1">
                                        <i class="ph ph-folders"></i>
                                        {{ $category->children_count }} {{ Str::plural('subcategory', $category->children_count) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 bg-base-100 rounded-lg border border-base-200">
                <div class="text-6xl mb-4"><i class="ph ph-folder-dashed text-base-content/30"></i></div>
                <h2 class="text-xl font-bold mb-2">No categories yet</h2>
                <p class="text-base-content/60 mb-6">Categories will appear here once created.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="ph ph-house"></i>
                    Back to Home
                </a>
            </div>
        @endif
    </div>

</x-layouts.public>

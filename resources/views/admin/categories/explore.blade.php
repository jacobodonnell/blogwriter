<x-layouts.admin title="Explore Categories">
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.categories.index') }}">Categories</a></li>
        <li>Explore</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">
                    <i class="ph ph-{{ $category ? 'folder' : 'folders' }} text-primary mr-2"></i>
                    {{ $category ? $category->name : 'Explore Categories' }}
                </h1>

                @if($category?->description)
                    <p class="text-base-content/70 text-lg max-w-2xl mt-2">{{ $category->description }}</p>
                @endif

                <p class="text-sm text-base-content/60 mt-2">
                    {{ $articleCount }} {{ Str::plural('article', $articleCount) }}
                    @if($photoCount > 0)
                        &middot; {{ $photoCount }} {{ Str::plural('photo', $photoCount) }}
                    @endif
                </p>
            </div>
        </div>

        {{-- View Switching Tabs --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                <i class="ph ph-table text-lg"></i>
                Table
            </a>
            <a href="{{ route('admin.categories.explore') }}" class="btn btn-ghost btn-sm btn-active gap-1">
                <i class="ph ph-folders text-lg"></i>
                Explore
            </a>
        </div>

        {{-- Explore Content --}}
        @include('admin.categories._explore-content')
    </div>
</x-layouts.admin>

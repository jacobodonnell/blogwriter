<x-layouts.admin title="Explore Categories">
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.categories.index') }}">Categories</a></li>
        <li>Explore</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
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

        {{-- Explore Content (header + filters + content, all inside morph target) --}}
        @include('admin.categories._explore-content')
    </div>
</x-layouts.admin>

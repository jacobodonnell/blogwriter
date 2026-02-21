<x-layouts.admin title="Photos">
    <x-slot:breadcrumb>
        <li>Photos</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{
            filtersOpen: $persist(true).as('admin_photos_filters_open'),
         }">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">Photos</h1>
                <p class="text-base-content/70 mt-1">Manage your photo library.</p>
            </div>
            <div class="flex gap-2">
                {{-- Filters Toggle --}}
                <button class="btn btn-ghost" @click="filtersOpen = !filtersOpen">
                    <i class="ph ph-funnel text-xl"></i>
                    Filters
                    @if($activeFilterCount > 0)
                        <span class="badge badge-sm badge-primary">{{ $activeFilterCount }}</span>
                    @endif
                    <i class="ph ph-caret-down text-sm transition-transform duration-200" :class="filtersOpen && 'rotate-180'"></i>
                </button>

                <a href="{{ route('photos.index') }}" class="btn btn-ghost">
                    <i class="ph ph-eye text-xl"></i>
                    <span class="hidden sm:inline">View Photos</span>
                </a>
                <a href="{{ route('admin.photos.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus text-xl"></i>
                    <span class="hidden sm:inline">New Photo</span>
                </a>
            </div>
        </div>

        {{-- Collapsible Filters --}}
        <x-admin.filter-banner :action="route('admin.photos.index')" target="photos-grid"
            :clearRoute="route('admin.photos.index')" :activeFilterCount="$activeFilterCount">
            <x-filters.search placeholder="Search by alt text, slug, or caption..." />
            <x-filters.category-select :categories="$categories" />
            <x-filters.select name="status" label="Status"
                :options="['published' => 'Published', 'draft' => 'Draft']"
                emptyLabel="All Status" />
            <x-filters.per-page :options="[12, 24, 48]" :default="$perPage" />
        </x-admin.filter-banner>

        {{-- Photos Grid --}}
        @include('admin.photos._grid')
    </div>
</x-layouts.admin>

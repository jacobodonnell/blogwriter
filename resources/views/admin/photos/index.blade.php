<x-layouts.admin title="Photos">
    <x-slot:breadcrumb>
        <li>Photos</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">Photos</h1>
                <p class="text-base-content/70 mt-1">Manage your photo library.</p>
            </div>
            <div class="flex gap-2">
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

        {{-- Filters --}}
        <x-filter-banner :action="route('admin.photos.index')" target="photos-grid"
            :clearRoute="route('admin.photos.index')" persistKey="admin_photos_filters_open"
            :defaultOpen="true" :filterParams="['search', 'category', 'status']">
            <x-filter-banner.search placeholder="Search by alt text, slug, or caption..." />
            <x-filter-banner.category-select :categories="$categories" />
            <x-filter-banner.select name="status" label="Status"
                :options="['public' => 'Public', 'private' => 'Private']"
                emptyLabel="All Status" />
            <x-filter-banner.per-page :options="[12, 24, 48]" :default="$perPage" />
        </x-filter-banner>

        {{-- Photos Grid --}}
        @include('admin.photos._grid')
    </div>
</x-layouts.admin>

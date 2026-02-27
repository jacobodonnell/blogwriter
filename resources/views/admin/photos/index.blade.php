<x-layouts.admin title="Photos">
    <x-slot:breadcrumb>
        <li>Photos</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{ deleteAction: '', deleteArticleCount: 0 }">
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

        {{-- Delete Confirmation Modal --}}
        <x-editor-modal x-ref="deleteModal" title="Delete Photo">
            <p>Are you sure you want to delete this photo?</p>
            <p x-show="deleteArticleCount > 0" x-cloak class="text-error font-semibold mt-2">
                This photo will be removed from
                <span x-text="deleteArticleCount"></span>
                <span x-text="deleteArticleCount === 1 ? 'article' : 'articles'"></span>
                as a featured image.
            </p>
            <p class="mt-2">This action cannot be undone.</p>
            <x-slot:actions>
                <form method="POST" :action="deleteAction" x-target="photos-grid" @ajax:success="$refs.deleteModal.close()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </x-slot:actions>
        </x-editor-modal>
    </div>
</x-layouts.admin>

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
        <div x-show="filtersOpen" x-collapse x-cloak class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.photos.index') }}"
                      x-target="photos-grid"
                      id="photos-filter-form"
                      class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto_auto] gap-4 items-end">

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Search</span>
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by alt text, slug, or caption..."
                               class="input input-bordered w-full"
                               @input.debounce.400ms="$el.form.requestSubmit()" />
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Category</span>
                        </label>
                        <x-category-select :categories="$categories"
                            name="category" emptyLabel="All Categories"
                            :selected="request('category')" :useSlug="true"
                            @change="$el.form.requestSubmit()"
                            class="select select-bordered w-full md:w-auto" />
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered w-full md:w-auto" @change="$el.form.requestSubmit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Per Page</span>
                        </label>
                        <select name="perPage" class="select select-bordered w-full md:w-auto" @change="$el.form.requestSubmit()">
                            @foreach([12, 24, 48] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Photos Grid --}}
        @include('admin.photos._grid')
    </div>
</x-layouts.admin>

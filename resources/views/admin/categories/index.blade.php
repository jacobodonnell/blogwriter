<x-layouts.admin title="Categories">
    <x-slot:breadcrumb>
        <li>Categories</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{
            filtersOpen: $persist(true).as('admin_categories_filters_open'),
         }">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">{{ $parent ? $parent->name : 'Categories' }}</h1>
                @if($breadcrumbs->isNotEmpty())
                    <nav class="text-sm breadcrumbs mt-1">
                        <ul>
                            <li>
                                <a href="{{ route('admin.categories.index') }}" class="link link-hover">
                                    Categories
                                </a>
                            </li>
                            @foreach($breadcrumbs as $crumb)
                                @if(!$loop->last)
                                    @php
                                        $crumbPath = $breadcrumbs->slice(0, $loop->index + 1)->pluck('slug')->implode('/');
                                    @endphp
                                    <li>
                                        <a href="{{ route('admin.categories.children', $crumbPath) }}" class="link link-hover">
                                            {{ $crumb->name }}
                                        </a>
                                    </li>
                                @else
                                    <li class="text-base-content/60">{{ $crumb->name }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                @else
                    <p class="text-base-content/70 mt-1">Manage article categories.</p>
                @endif
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

                <a href="{{ route('categories.index') }}" class="btn btn-ghost">
                    <i class="ph ph-eye text-xl"></i>
                    <span class="hidden sm:inline">Manage Categories</span>
                </a>
                <button
                    class="btn btn-primary"
                    onclick="document.getElementById('add-category-modal').showModal()">
                    <i class="ph ph-plus text-xl"></i>
                    <span class="hidden sm:inline">{{ $parent ? 'Add Subcategory' : 'Add Category' }}</span>
                </button>
            </div>
        </div>

        {{-- Collapsible Filters --}}
        <div x-show="filtersOpen" x-collapse x-cloak class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.categories.index') }}"
                      x-target="categories-table"
                      id="categories-filter-form"
                      class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto_auto] gap-4 items-end">

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Search</span>
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by name or slug..."
                               class="input input-bordered w-full"
                               @input.debounce.400ms="$el.form.requestSubmit()" />
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Content Type</span>
                        </label>
                        <select name="content_type" class="select select-bordered w-full md:w-auto" @change="$el.form.requestSubmit()">
                            <option value="">All Types</option>
                            <option value="articles" {{ request('content_type') == 'articles' ? 'selected' : '' }}>Articles</option>
                            <option value="photos" {{ request('content_type') == 'photos' ? 'selected' : '' }}>Photos</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Per Page</span>
                        </label>
                        <select name="perPage" class="select select-bordered w-full md:w-auto" @change="$el.form.requestSubmit()">
                            @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Categories List --}}
        @include('admin.categories._table')
    </div>

    {{-- Add Category Modal --}}
    <x-editor-modal
        id="add-category-modal"
        x-init
        @category:created.window="$el.close()"
        @modal:close.window="$el.close()"
        :title="$parent ? 'Add Subcategory in ' . $parent->name : 'Add New Category'">
        <div id="add-category-form">
            @include('admin.categories._add-form')
        </div>
    </x-editor-modal>
</x-layouts.admin>

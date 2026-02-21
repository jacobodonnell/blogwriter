<x-layouts.admin title="Categories">
    <x-slot:breadcrumb>
        <li>Categories</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
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
                <a href="{{ route('categories.index') }}" class="btn btn-ghost">
                    <i class="ph ph-eye text-xl"></i>
                    <span class="hidden sm:inline">View Categories</span>
                </a>
                <button
                    class="btn btn-primary"
                    onclick="document.getElementById('add-category-modal').showModal()">
                    <i class="ph ph-plus text-xl"></i>
                    <span class="hidden sm:inline">{{ $parent ? 'Add Subcategory' : 'Add Category' }}</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <x-filter-banner :action="route('admin.categories.index')" target="categories-table"
            :clearRoute="route('admin.categories.index')" persistKey="admin_categories_filters_open"
            :filterParams="['search', 'content_type']">
            <x-filter-banner.search placeholder="Search by name or slug..." />
            <x-filter-banner.select name="content_type" label="Content Type"
                :options="['articles' => 'Articles', 'photos' => 'Photos']"
                emptyLabel="All Types" />
            <x-filter-banner.per-page :options="[10, 20, 50, 100]" :default="$perPage" />
        </x-filter-banner>

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

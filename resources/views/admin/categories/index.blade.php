<x-layouts.admin title="Categories">
    <x-slot:breadcrumb>
        <li>Categories</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{
            columns: {
                slug: $persist(true).as('categories_col_slug'),
                parent: $persist(true).as('categories_col_parent'),
                articles: $persist(true).as('categories_col_articles'),
                photos: $persist(true).as('categories_col_photos'),
                subcategories: $persist(true).as('categories_col_subcategories'),
                description: $persist(false).as('categories_col_description'),
            },
            toggle(col) {
                this.columns[col] = !this.columns[col];
            },
            deleteAction: '',
         }">
        {{-- View Switching Tabs --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm btn-active gap-1">
                <i class="ph ph-table text-lg"></i>
                Table
            </a>
            <a href="{{ route('admin.categories.explore') }}" class="btn btn-ghost btn-sm gap-1">
                <i class="ph ph-folders text-lg"></i>
                Explore
            </a>
        </div>

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">Categories</h1>
                <p class="text-base-content/70 mt-1">Manage article categories.</p>
            </div>
            <div class="flex gap-2">
                <button
                    class="btn btn-primary"
                    onclick="document.getElementById('add-category-modal').showModal()">
                    <i class="ph ph-plus text-xl"></i>
                    <span class="hidden sm:inline">Add Category</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <x-filter-banner :action="route('admin.categories.index')" target="categories-table"
            :clearRoute="route('admin.categories.index')" persistKey="admin_categories_filters_open"
            :defaultOpen="true" :filterParams="['search', 'content_type', 'parent_id']">
            <x-slot:toolbar>
                {{-- Columns Toggle --}}
                <div class="dropdown md:dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-columns text-lg"></i>
                        <span class="hidden sm:inline">Columns</span>
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-56">
                        @php
                            $columnToggles = [
                                ['key' => 'slug', 'label' => 'Slug'],
                                ['key' => 'parent', 'label' => 'Parent'],
                                ['key' => 'articles', 'label' => 'Articles'],
                                ['key' => 'photos', 'label' => 'Photos'],
                                ['key' => 'subcategories', 'label' => 'Subcategories'],
                                ['key' => 'description', 'label' => 'Description'],
                            ];
                        @endphp

                        @foreach ($columnToggles as $col)
                            <li>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.{{ $col['key'] }}" @change="toggle('{{ $col['key'] }}')" />
                                    <span>{{ $col['label'] }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-slot:toolbar>

            <x-filter-banner.search placeholder="Search by name or slug..." />
            <x-filter-banner.select name="content_type" label="Content Type"
                :options="['articles' => 'Articles', 'photos' => 'Photos']"
                emptyLabel="All Types" />
            <x-filter-banner.select name="parent_id" label="Parent"
                :options="$allCategories->pluck('name', 'id')->toArray()"
                emptyLabel="All Categories" />
            <x-filter-banner.per-page :options="[10, 20, 50, 100]" :default="$perPage" />
        </x-filter-banner>

        {{-- Categories List --}}
        @include('admin.categories._table')

        {{-- Delete Confirmation Modal --}}
        <x-editor-modal x-ref="deleteModal" title="Delete Category">
            <p>Are you sure you want to delete this category? This action cannot be undone.</p>
            <x-slot:actions>
                <form method="POST" :action="deleteAction" x-target="categories-table" @ajax:success="$refs.deleteModal.close()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </x-slot:actions>
        </x-editor-modal>
    </div>

    {{-- Add Category Modal --}}
    <x-editor-modal
        id="add-category-modal"
        x-init
        @category:created.window="$el.close()"
        @modal:close.window="$el.close()"
        title="Add New Category">
        <div id="add-category-form">
            @include('admin.categories._add-form', ['parent' => null])
        </div>
    </x-editor-modal>
</x-layouts.admin>

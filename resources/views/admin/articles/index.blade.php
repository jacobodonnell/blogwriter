<x-layouts.admin title="Articles">
    <x-slot:breadcrumb>
        <li>Articles</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{
            columns: {
                featuredImage: $persist(false).as('articles_col_featuredImage'),
                title: $persist(true).as('articles_col_title'),
                status: $persist(true).as('articles_col_status'),
                categories: $persist(true).as('articles_col_categories'),
                publishedAt: $persist(false).as('articles_col_publishedAt'),
                createdAt: $persist(false).as('articles_col_createdAt'),
                updatedAt: $persist(true).as('articles_col_updatedAt'),
            },
            toggle(col) {
                this.columns[col] = !this.columns[col];
            }
         }">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">Articles</h1>
                <p class="text-base-content/70 mt-1">Manage your blog articles.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('articles.index') }}" class="btn btn-ghost">
                    <i class="ph ph-eye text-xl"></i>
                    <span class="hidden sm:inline">View Articles</span>
                </a>
                <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus text-xl"></i>
                    <span class="hidden sm:inline">New Article</span>
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <x-filter-banner :action="route('admin.articles.index')" target="articles-table"
            :clearRoute="route('admin.articles.index')" persistKey="admin_articles_filters_open"
            :filterParams="['search', 'category', 'status']">
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
                                ['key' => 'featuredImage', 'label' => 'Featured Image'],
                                ['key' => 'title', 'label' => 'Title'],
                                ['key' => 'status', 'label' => 'Status'],
                                ['key' => 'categories', 'label' => 'Category'],
                                ['key' => 'publishedAt', 'label' => 'Published At'],
                                ['key' => 'createdAt', 'label' => 'Created At'],
                                ['key' => 'updatedAt', 'label' => 'Updated At'],
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

            {{-- Preserve column header sort state --}}
            <input type="hidden" name="sort" value="{{ $currentSort }}" />
            <input type="hidden" name="direction" value="{{ $currentDirection }}" />

            <x-filter-banner.search placeholder="Search by title or slug..." />
            <x-filter-banner.category-select :categories="$categories" />
            <x-filter-banner.select name="status" label="Status"
                :options="['published' => 'Published', 'draft' => 'Draft']"
                emptyLabel="All Status" />
            <x-filter-banner.per-page :options="[10, 20, 50, 100]" :default="$perPage" />
        </x-filter-banner>

        {{-- Articles List --}}
        @include('admin.articles._table')
    </div>
</x-layouts.admin>

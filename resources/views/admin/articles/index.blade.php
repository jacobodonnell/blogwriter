<x-layouts.admin title="Articles">

    @php
        $activeFilterCount = collect([request('search'), request('category'), request('status')])->filter()->count();
    @endphp

    <div class="space-y-6"
         x-data="{
            columns: {
                featuredImage: localStorage.getItem('articles_col_featuredImage') === 'true',
                title: localStorage.getItem('articles_col_title') !== 'false',
                status: localStorage.getItem('articles_col_status') !== 'false',
                categories: localStorage.getItem('articles_col_categories') !== 'false',
                publishedAt: localStorage.getItem('articles_col_publishedAt') === 'true',
                createdAt: localStorage.getItem('articles_col_createdAt') === 'true',
                updatedAt: localStorage.getItem('articles_col_updatedAt') !== 'false',
            },
            filtersOpen: false,
            toggle(col) {
                this.columns[col] = !this.columns[col];
                localStorage.setItem('articles_col_' + col, this.columns[col]);
            }
         }">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">Articles</h1>
                <p class="text-base-content/70 mt-1">Manage your blog articles.</p>
            </div>
            <div class="flex gap-2">
                {{-- Mobile Filters Toggle --}}
                <button class="btn btn-ghost md:hidden" @click="filtersOpen = !filtersOpen">
                    <i class="ph ph-funnel text-xl"></i>
                    Filters
                    @if($activeFilterCount > 0)
                        <span class="badge badge-sm badge-primary">{{ $activeFilterCount }}</span>
                    @endif
                </button>

                {{-- Columns Toggle --}}
                <div class="dropdown md:dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost">
                        <i class="ph ph-columns text-xl"></i>
                        <span class="hidden sm:inline">Columns</span>
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-56">
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.featuredImage" @change="toggle('featuredImage')" />
                                <span>Featured Image</span>
                            </label>
                        </li>
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.title" @change="toggle('title')" />
                                <span>Title</span>
                            </label>
                        </li>
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.status" @change="toggle('status')" />
                                <span>Status</span>
                            </label>
                        </li>
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.categories" @change="toggle('categories')" />
                                <span>Category</span>
                            </label>
                        </li>
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.publishedAt" @change="toggle('publishedAt')" />
                                <span>Published At</span>
                            </label>
                        </li>
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.createdAt" @change="toggle('createdAt')" />
                                <span>Created At</span>
                            </label>
                        </li>
                        <li>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-sm" :checked="columns.updatedAt" @change="toggle('updatedAt')" />
                                <span>Updated At</span>
                            </label>
                        </li>
                    </ul>
                </div>

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

        {{-- Filters: always visible on md+, toggle on mobile --}}
        <div class="card bg-base-100 shadow hidden md:block" :class="filtersOpen && '!block'">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.articles.index') }}"
                      x-target="articles-table"
                      id="articles-filter-form"
                      class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto_auto] gap-4 items-end">
                    {{-- Preserve sort params --}}
                    <input type="hidden" name="sort" value="{{ $currentSort }}" />
                    <input type="hidden" name="direction" value="{{ $currentDirection }}" />

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Search</span>
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by title or slug..."
                               class="input input-bordered w-full"
                               @input.debounce.400ms="$el.form.requestSubmit()" />
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Category</span>
                        </label>
                        <select name="category" class="select select-bordered w-full md:w-auto" onchange="this.form.requestSubmit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $rootCat)
                                <option value="{{ $rootCat->id }}" {{ request('category') == $rootCat->id ? 'selected' : '' }}>
                                    {{ $rootCat->name }}
                                </option>
                                @foreach($rootCat->children as $childCat)
                                    <option value="{{ $childCat->id }}" {{ request('category') == $childCat->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;└ {{ $childCat->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered w-full md:w-auto" onchange="this.form.requestSubmit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-4 md:block">
                        <label class="label shrink-0">
                            <span class="label-text">Per Page</span>
                        </label>
                        <select name="perPage" class="select select-bordered w-full md:w-auto" onchange="this.form.requestSubmit()">
                            @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($activeFilterCount > 0)
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Articles List --}}
        @include('admin.articles._table')
    </div>
</x-layouts.admin>

<x-layouts.admin>
    @section('title', 'Articles')

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
            toggle(col) {
                this.columns[col] = !this.columns[col];
                localStorage.setItem('articles_col_' + col, this.columns[col]);
            }
         }">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Articles</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your blog articles.</p>
            </div>
            <div class="flex gap-2">
                {{-- Columns Toggle --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost">
                        <i class="ph ph-columns text-xl mr-2"></i>
                        Columns
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
                                <span>Categories</span>
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
                    <i class="ph ph-eye text-xl mr-2"></i>
                    View Articles
                </a>
                <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus text-xl mr-2"></i>
                    New Article
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.articles.index') }}"
                      x-target="articles-table"
                      id="articles-filter-form"
                      class="flex flex-wrap gap-4 items-end">
                    {{-- Preserve sort params --}}
                    <input type="hidden" name="sort" value="{{ $currentSort }}" />
                    <input type="hidden" name="direction" value="{{ $currentDirection }}" />

                    <div class="form-control w-full md:w-64">
                        <label class="label">
                            <span class="label-text">Search</span>
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by title or slug..."
                               class="input input-bordered"
                               @input.debounce.400ms="$el.form.requestSubmit()" />
                    </div>

                    <div class="form-control w-full md:w-48">
                        <label class="label">
                            <span class="label-text">Category</span>
                        </label>
                        <select name="category" class="select select-bordered" onchange="this.form.requestSubmit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control w-full md:w-48">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered" onchange="this.form.requestSubmit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <div class="form-control w-full md:w-32">
                        <label class="label">
                            <span class="label-text">Per Page</span>
                        </label>
                        <select name="perPage" class="select select-bordered" onchange="this.form.requestSubmit()">
                            @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(request('category') || request('status') || request('search'))
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

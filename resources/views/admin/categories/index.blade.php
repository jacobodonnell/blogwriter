<x-layouts.admin title="Categories">

    <div class="space-y-6" x-data="{ currentParent: @js((string) ($parent?->id ?? '')) }">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold">Categories</h1>
            <p class="text-base-content/70 mt-1">Manage article categories.</p>
        </div>

        {{-- Add Category Form --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">Add New Category</h2>
                <form method="POST"
                      action="{{ route('admin.categories.store') }}"
                      x-target="categories-table"
                      x-data="{ name: '', slug: '' }"
                      class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <input type="hidden" name="parent_id" :value="currentParent">
                    <div class="form-control flex-1 min-w-[150px]">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text"
                               name="name"
                               x-model="name"
                               @blur="if (!slug) { slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') }"
                               class="input input-bordered"
                               placeholder="Category name"
                               required>
                    </div>
                    <div class="form-control flex-1 min-w-[150px]">
                        <label class="label">
                            <span class="label-text">Slug (optional)</span>
                        </label>
                        <input type="text"
                               name="slug"
                               x-model="slug"
                               class="input input-bordered"
                               placeholder="auto-generated">
                    </div>
                    <div class="form-control flex-[2] min-w-[200px]">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <input type="text" name="description" class="input input-bordered" placeholder="Brief description">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>

        {{-- Parent Filter --}}
        <form method="GET"
              action="{{ route('admin.categories.index') }}"
              x-target.push="categories-table"
              class="flex items-center gap-3">
            <label class="label">
                <span class="label-text font-medium">Viewing:</span>
            </label>
            <x-category-select
                :categories="$allCategories"
                name="parent"
                empty-label="Root Categories"
                x-model="currentParent"
                onchange="this.form.requestSubmit()"
                class="select select-bordered select-sm"
            />
        </form>

        {{-- Categories List --}}
        @include('admin.categories._table')
    </div>
</x-layouts.admin>

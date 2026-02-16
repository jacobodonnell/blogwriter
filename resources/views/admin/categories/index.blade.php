<x-layouts.admin>
    @section('title', 'Categories')

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold">Categories</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage article categories.</p>
        </div>

        {{-- Add Category Form --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">Add New Category</h2>
                <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-4 items-end">
                    @csrf
                    <div class="form-control flex-1">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" class="input input-bordered" placeholder="Category name" required>
                    </div>
                    <div class="form-control flex-1">
                        <label class="label">
                            <span class="label-text">Slug (optional)</span>
                        </label>
                        <input type="text" name="slug" class="input input-bordered" placeholder="auto-generated">
                    </div>
                    <div class="form-control flex-[2]">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <input type="text" name="description" class="input input-bordered" placeholder="Brief description">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>

        {{-- Categories List --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body p-0">
                @if($categories->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Slug</th>
                                    <th>Articles</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td>
                                            <div class="font-semibold">
                                                {{ $category->name }}
                                                <a href="{{ route('category.show', $category->slug) }}" class="inline-block align-middle ml-1 opacity-50 hover:opacity-100" title="View category">
                                                    <i class="ph ph-eye text-sm"></i>
                                                </a>
                                            </div>
                                            @if($category->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($category->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="text-sm text-gray-500">
                                            {{ $category->slug }}
                                        </td>
                                        <td>
                                            <span class="badge badge-sm">{{ $category->articles->count() }}</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-ghost">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                @if($category->articles->count() === 0)
                                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-ghost text-error">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500">No categories yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
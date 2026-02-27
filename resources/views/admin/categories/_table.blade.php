{{-- Table card: targeted by Alpine AJAX via #categories-table --}}
<div id="categories-table"
     class="card bg-base-100 shadow"
     x-init
     @category:created.window="$ajax(window.location.href)">
    <div class="card-body p-0">

        @if($categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th x-show="columns.description" x-cloak>Description</th>
                            <th x-show="columns.slug" x-cloak>Slug</th>
                            <th x-show="columns.parent" x-cloak>Parent</th>
                            <th x-show="columns.articles" x-cloak>Articles</th>
                            <th x-show="columns.photos" x-cloak>Photos</th>
                            <th x-show="columns.subcategories" x-cloak>Subcategories</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold">
                                            {{ $category->name }}
                                            <x-admin.icon-button tooltip="View category" href="{{ $category->adminExploreUrl() }}" icon="eye" class="btn-xs opacity-50 hover:opacity-100" data-test="view-category" />
                                        </div>
                                    </div>
                                </td>
                                <td x-show="columns.description" x-cloak class="text-sm text-base-content/60">
                                    {{ $category->description ? Str::limit($category->description, 60) : '—' }}
                                </td>
                                <td x-show="columns.slug" x-cloak class="text-sm text-base-content/60">
                                    {{ $category->slug }}
                                </td>
                                <td x-show="columns.parent" x-cloak>
                                    @if($category->parent)
                                        <span class="badge badge-sm badge-outline">{{ $category->parent->name }}</span>
                                    @else
                                        <span class="text-base-content/40 text-sm">—</span>
                                    @endif
                                </td>
                                <td x-show="columns.articles" x-cloak>
                                    <span class="badge badge-sm">{{ $category->articles_count }}</span>
                                </td>
                                <td x-show="columns.photos" x-cloak>
                                    <span class="badge badge-sm">{{ $category->photos_count }}</span>
                                </td>
                                <td x-show="columns.subcategories" x-cloak>
                                    @if($category->children_count > 0)
                                        <span class="badge badge-sm badge-outline">{{ $category->children_count }}</span>
                                    @else
                                        <span class="text-base-content/40 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button tooltip="Edit" href="{{ route('admin.categories.edit', $category) }}" icon="pencil-simple" />
                                        @if($category->articles_count === 0 && $category->photos_count === 0 && $category->children_count === 0)
                                            <div class="tooltip" data-tip="Delete">
                                                <button type="button"
                                                        class="btn btn-sm btn-ghost text-error"
                                                        @click="deleteAction = '{{ route('admin.categories.destroy', $category) }}'; $refs.deleteModal.showModal()">
                                                    <i class="ph ph-trash text-lg"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($categories instanceof \Illuminate\Pagination\AbstractPaginator && $categories->hasPages())
                <div class="p-4 border-t border-base-300">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <p class="text-base-content/60">No categories yet.</p>
            </div>
        @endif
    </div>
</div>

{{-- Table card: targeted by Alpine AJAX via #categories-table --}}
<div id="categories-table" class="card bg-base-100 shadow" x-init="currentParent = @js((string) ($parent?->id ?? ''))">
    <div class="card-body p-0">

        {{-- Breadcrumb Navigation --}}
        @if($breadcrumbs->isNotEmpty())
            <div class="px-4 pt-4 pb-2">
                <nav class="text-sm breadcrumbs overflow-x-auto">
                    <ul class="flex-wrap">
                        <li>
                            <a href="{{ route('admin.categories.index') }}"
                               x-target="categories-table"
                               class="link link-hover">
                                <i class="ph ph-house mr-1"></i> Root
                            </a>
                        </li>
                        @foreach($breadcrumbs as $crumb)
                            @if(!$loop->last)
                                <li>
                                    <a href="{{ route('admin.categories.index', ['parent' => $crumb->id]) }}"
                                       x-target="categories-table"
                                       class="link link-hover">
                                        {{ $crumb->name }}
                                    </a>
                                </li>
                            @else
                                <li class="text-base-content/60">{{ $crumb->name }}</li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>
        @endif

        @if($categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Articles</th>
                            <th>Subcategories</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold">
                                            @if($category->children_count > 0)
                                                <a href="{{ route('admin.categories.index', ['parent' => $category->id]) }}"
                                                   x-target="categories-table"
                                                   class="link link-hover inline-flex items-center gap-1">
                                                    {{ $category->name }}
                                                    <i class="ph ph-caret-right text-xs"></i>
                                                </a>
                                            @else
                                                {{ $category->name }}
                                            @endif
                                            <a href="{{ route('category.show', $category->slug) }}" class="inline-block align-middle ml-1 opacity-50 hover:opacity-100" title="View category">
                                                <i class="ph ph-eye text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @if($category->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($category->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-500">
                                    {{ $category->slug }}
                                </td>
                                <td>
                                    <span class="badge badge-sm">{{ $category->articles_count }}</span>
                                </td>
                                <td>
                                    @if($category->children_count > 0)
                                        <span class="badge badge-sm badge-outline">{{ $category->children_count }}</span>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-ghost">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        @if($category->articles_count === 0 && $category->children_count === 0)
                                            <form method="POST"
                                                  action="{{ route('admin.categories.destroy', $category) }}"
                                                  x-target="categories-table"
                                                  class="inline"
                                                  onsubmit="return confirm('Delete this category?');">
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
                @if($parent)
                    <p class="text-gray-500">No subcategories in {{ $parent->name }}.</p>
                @else
                    <p class="text-gray-500">No categories yet.</p>
                @endif
            </div>
        @endif
    </div>
</div>

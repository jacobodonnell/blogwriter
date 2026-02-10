<x-layouts.admin>
    @section('title', 'Articles')

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Articles</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your blog articles.</p>
            </div>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                <i class="ph ph-plus text-xl mr-2"></i>
                New Article
            </a>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.articles.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="form-control w-full md:w-48">
                        <label class="label">
                            <span class="label-text">Category</span>
                        </label>
                        <select name="category" class="select select-bordered" onchange="this.form.submit()">
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
                        <select name="status" class="select select-bordered" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Hidden</option>
                        </select>
                    </div>

                    @if(request('category') || request('status'))
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Articles List --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body p-0">
                @if($articles->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Status</th>
                                    <th>Categories</th>
                                    <th>Updated</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($articles as $article)
                                    <tr>
                                        <td>
                                            <div class="font-semibold">{{ $article->title }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($article->slug, 40) }}</div>
                                        </td>
                                        <td>
                                            <span @class([
                                                'badge',
                                                'badge-success' => $article->status === 'published',
                                                'badge-warning' => $article->status === 'draft',
                                                'badge-ghost' => $article->status === 'hidden',
                                            ])>
                                                {{ ucfirst($article->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($article->categories as $category)
                                                <span class="badge badge-sm badge-outline mr-1">{{ $category->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-sm text-gray-500">
                                            {{ $article->updated_at->diffForHumans() }}
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-ghost" title="Edit">
                                                    <i class="ph ph-pencil-simple text-lg"></i>
                                                </a>
                                                <a href="{{ $article->permalink() }}" target="_blank" class="btn btn-sm btn-ghost" title="View">
                                                    <i class="ph ph-eye text-lg"></i>
                                                </a>
                                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost text-error" title="Delete">
                                                        <i class="ph ph-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="p-4 border-t">
                        {{ $articles->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500 mb-4">No articles found.</p>
                        @if(request('category') || request('status'))
                            <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost">Clear Filters</a>
                        @else
                            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">Create First Article</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
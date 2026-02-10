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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <a href="{{ $article->permalink() }}" target="_blank" class="btn btn-sm btn-ghost" title="View">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-ghost text-error" title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
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
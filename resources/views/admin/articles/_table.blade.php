{{-- Table card: targeted by Alpine AJAX via #articles-table --}}
<div id="articles-table" class="card bg-base-100 shadow">
    <div class="card-body p-0">
        @if($articles->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th x-show="columns.featuredImage" x-cloak>
                                Image
                            </th>
                            <th x-show="columns.title">
                                @include('admin.articles._sort-header', ['column' => 'title', 'label' => 'Article'])
                            </th>
                            <th x-show="columns.status">
                                @include('admin.articles._sort-header', ['column' => 'status', 'label' => 'Status'])
                            </th>
                            <th x-show="columns.categories">
                                Category
                            </th>
                            <th x-show="columns.publishedAt" x-cloak>
                                @include('admin.articles._sort-header', ['column' => 'published_at', 'label' => 'Published'])
                            </th>
                            <th x-show="columns.createdAt" x-cloak>
                                @include('admin.articles._sort-header', ['column' => 'created_at', 'label' => 'Created'])
                            </th>
                            <th x-show="columns.updatedAt">
                                @include('admin.articles._sort-header', ['column' => 'updated_at', 'label' => 'Updated'])
                            </th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                            <tr>
                                <td x-show="columns.featuredImage" x-cloak>
                                    @if($article->photo_id && $article->featuredPhoto)
                                        <a href="{{ route('admin.photos.edit', $article->featuredPhoto) }}" class="block">
                                            <img src="{{ $article->featuredPhoto->getFirstMediaUrl('image', 'thumbnail') }}"
                                                 alt=""
                                                 class="w-12 h-12 rounded object-cover" />
                                        </a>
                                        <span class="badge badge-success badge-xs mt-1">Uploaded</span>
                                    @elseif($article->meta['featured_image_url'] ?? null)
                                        <img src="{{ $article->meta['featured_image_url'] }}"
                                             alt=""
                                             class="w-12 h-12 rounded object-cover" />
                                        <span class="badge badge-info badge-xs mt-1">External</span>
                                    @else
                                        <span class="text-base-content/40 text-sm">None</span>
                                    @endif
                                </td>
                                <td x-show="columns.title">
                                    <div class="font-semibold">{{ $article->title }}</div>
                                    <div class="text-sm text-base-content/60">{{ Str::limit($article->slug, 40) }}</div>
                                </td>
                                <td x-show="columns.status">
                                    <span @class([
                                        'badge',
                                        'badge-success' => $article->status->value === 'published',
                                        'badge-warning' => $article->status->value === 'draft',
                                    ])>
                                        {{ $article->status->label() }}
                                    </span>
                                </td>
                                <td x-show="columns.categories">
                                    @if($article->category)
                                        <span class="badge badge-sm badge-outline">{{ $article->category->name }}</span>
                                    @else
                                        <span class="text-base-content/40 text-sm">—</span>
                                    @endif
                                </td>
                                <td x-show="columns.publishedAt" x-cloak class="text-sm text-base-content/60">
                                    {{ $article->published_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td x-show="columns.createdAt" x-cloak class="text-sm text-base-content/60">
                                    {{ $article->created_at->diffForHumans() }}
                                </td>
                                <td x-show="columns.updatedAt" class="text-sm text-base-content/60">
                                    {{ $article->updated_at->diffForHumans() }}
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-ghost" title="Edit">
                                            <i class="ph ph-pencil-simple text-lg"></i>
                                        </a>
                                        @if($article->isPublished())
                                            <a href="{{ $article->permalink() }}" class="btn btn-sm btn-ghost" title="View Published">
                                                <i class="ph ph-eye text-lg"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-sm btn-ghost" title="Preview Draft">
                                                <i class="ph ph-eye text-lg"></i>
                                            </a>
                                        @endif
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
                <p class="text-base-content/60 mb-4">No articles found.</p>
                @if(request('category') || request('status') || request('search'))
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost">Clear Filters</a>
                @else
                    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">Create First Article</a>
                @endif
            </div>
        @endif
    </div>
</div>

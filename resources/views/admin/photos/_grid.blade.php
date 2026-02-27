<div id="photos-grid">
    @if($photos->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($photos as $photo)
                <article class="card bg-base-100 shadow">
                    <figure class="relative aspect-square">
                        <img src="{{ $photo->image_url }}"
                             alt="{{ $photo->alt_text }}"
                             class="w-full h-full object-cover">

                        {{-- Status Badge --}}
                        <div class="absolute top-2 left-2">
                            <span @class([
                                'badge',
                                'badge-success' => $photo->status->value === 'public',
                                'badge-warning' => $photo->status->value === 'private',
                            ])>
                                {{ $photo->status->label() }}
                            </span>
                        </div>
                    </figure>

                    <div class="card-body">
                        <h3 class="card-title text-base line-clamp-2">
                            {{ $photo->alt_text }}
                        </h3>

                        @if($photo->caption)
                            <p class="text-sm text-base-content/60 line-clamp-2">
                                {{ Str::limit(strip_tags(Str::markdown($photo->caption)), 80) }}
                            </p>
                        @endif

                        {{-- Metadata --}}
                        <div class="text-sm text-base-content/60 mt-2">
                            <div class="flex items-center gap-2">
                                <i class="ph ph-calendar-blank"></i>
                                {{ $photo->published_at?->format('M j, Y') ?? 'Not published' }}
                            </div>
                            @if($photo->articles()->count() > 0)
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="ph ph-article"></i>
                                    Used in {{ $photo->articles()->count() }} {{ Str::plural('article', $photo->articles()->count()) }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="card-actions justify-end mt-4">
                            <x-admin.icon-button tooltip="Edit" href="{{ route('admin.photos.edit', $photo) }}" icon="pencil-simple" />
                            <x-admin.icon-button tooltip="View details" href="{{ route('admin.photos.show', $photo) }}" icon="info" />

                            @if($photo->isPublic())
                                <x-admin.icon-button tooltip="View" href="{{ route('photos.show', $photo->slug) }}" icon="eye" />
                            @endif

                            <form method="POST"
                                  action="{{ route('admin.photos.destroy', $photo) }}"
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this photo?{{ $photo->articles()->count() > 0 ? ' This photo is used in ' . $photo->articles()->count() . ' article(s).' : '' }}');">
                                @csrf
                                @method('DELETE')
                                <x-admin.icon-button-submit tooltip="Delete" icon="trash" class="text-error" />
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $photos->links() }}
        </div>
    @else
        <div class="text-center py-12 card bg-base-100 shadow">
            <div class="card-body">
                <p class="text-base-content/60 mb-4">No photos found.</p>
                @if(request('search') || request('category') || request('status'))
                    <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost">Clear Filters</a>
                @else
                    <a href="{{ route('admin.photos.create') }}" class="btn btn-primary">Upload First Photo</a>
                @endif
            </div>
        </div>
    @endif
</div>

<x-layouts.public title="Photos - {{ config('app.name') }}">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-6xl mx-auto">

        {{-- Feed Header --}}
        <header class="mb-8">
            <h1 class="text-4xl font-bold mb-2">Photos</h1>
            <p class="text-base-content/60">A visual collection of moments and memories.</p>
        </header>

        {{-- Photos Grid --}}
        @if($photos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($photos as $photo)
                    {{-- h-entry for each photo --}}
                    <article class="h-entry card bg-base-100 shadow-sm border border-base-200 hover:shadow-md transition-shadow">
                        <figure class="aspect-square overflow-hidden">
                            <a href="{{ route('photos.show', $photo->slug) }}">
                                <img src="{{ $photo->image_url }}"
                                     alt="{{ $photo->alt_text }}"
                                     class="u-photo w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </a>
                        </figure>

                        @if($photo->caption)
                            <div class="card-body">
                                {{-- Caption excerpt --}}
                                <div class="e-content prose prose-sm max-w-none">
                                    {{ Str::limit(strip_tags(Str::markdown($photo->caption)), 120) }}
                                </div>

                                {{-- Published Date --}}
                                <div class="text-xs text-base-content/60 mt-2">
                                    <time class="dt-published"
                                          datetime="{{ $photo->published_at->toIso8601String() }}">
                                        {{ $photo->published_at->format('F j, Y') }}
                                    </time>
                                </div>

                                {{-- Read More Link --}}
                                @if(strlen($photo->caption) > 120)
                                    <a href="{{ route('photos.show', $photo->slug) }}"
                                       class="u-url link link-primary text-sm mt-2">
                                        View full photo →
                                    </a>
                                @endif
                            </div>
                        @else
                            {{-- No caption, just metadata --}}
                            <div class="card-body py-3">
                                <time class="dt-published text-xs text-base-content/60"
                                      datetime="{{ $photo->published_at->toIso8601String() }}">
                                    {{ $photo->published_at->format('F j, Y') }}
                                </time>
                                <a href="{{ route('photos.show', $photo->slug) }}"
                                   class="u-url link link-primary text-sm">
                                    View photo →
                                </a>
                            </div>
                        @endif

                        {{-- Hidden author info for h-entry --}}
                        <span class="p-author h-card hidden">
                            <span class="p-name">{{ \App\Models\User::first()?->name ?? 'Author' }}</span>
                        </span>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $photos->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="text-6xl mb-4">📷</div>
                <h2 class="text-2xl font-bold mb-2">No photos yet</h2>
                <p class="text-base-content/60">Check back soon for new photos.</p>
            </div>
        @endif
    </div>

</x-layouts.public>

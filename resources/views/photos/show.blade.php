<x-layouts.public :title="$photo->alt_text . ' - ' . config('app.name')">

    {{-- h-entry for IndieWeb --}}
    <article class="h-entry max-w-4xl mx-auto">

        {{-- Auth Toolbar --}}
        @auth
            <div class="flex items-center gap-2 mb-6">
                <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-images"></i>
                    All Photos
                </a>
                <a href="{{ route('admin.photos.edit', $photo) }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-pencil-simple"></i>
                    Edit
                </a>
            </div>
        @endauth

        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                <li><a href="{{ route('photos.index') }}" class="link link-hover">Photos</a></li>
                <li class="text-base-content/60 truncate max-w-xs">{{ Str::limit($photo->alt_text, 40) }}</li>
            </ul>
        </nav>

        {{-- Photo --}}
        <figure class="mb-8">
            <img src="{{ $photo->image_url }}"
                 alt="{{ $photo->alt_text }}"
                 class="u-photo w-full h-auto rounded-lg shadow-lg">
        </figure>

        {{-- Caption --}}
        @if($photo->caption)
            <div class="e-content prose prose-lg max-w-none mb-8">
                {!! Str::markdown($photo->caption) !!}
            </div>
        @endif

        {{-- Metadata Bar --}}
        <div class="flex flex-wrap items-center gap-4 text-sm text-base-content/60 mb-8 pb-8 border-b border-base-200">
            <div class="flex items-center gap-2">
                <i class="ph ph-calendar-blank"></i>
                <time class="dt-published" datetime="{{ $photo->published_at->toIso8601String() }}">
                    {{ $photo->published_at->format('F j, Y') }}
                </time>
            </div>

            @if($photo->taken_at)
                <div class="flex items-center gap-2">
                    <i class="ph ph-camera"></i>
                    <span>Taken {{ $photo->taken_at->format('F j, Y') }}</span>
                </div>
            @endif

            {{-- Hidden author for h-entry --}}
            <span class="p-author h-card hidden">
                <span class="p-name">{{ \App\Models\User::first()?->name ?? 'Author' }}</span>
            </span>
        </div>

        {{-- EXIF Details --}}
        @if(!empty($photo->meta))
            <details class="collapse collapse-arrow bg-base-200 rounded-lg mb-8">
                <summary class="collapse-title font-medium">
                    <i class="ph ph-info mr-2"></i>
                    Photo Details
                </summary>
                <div class="collapse-content">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-2">
                        @if($photo->meta['camera_model'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">Camera:</dt>
                                <dd class="text-base-content">{{ $photo->meta['camera_model'] }}</dd>
                            </div>
                        @endif

                        @if($photo->meta['lens'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">Lens:</dt>
                                <dd class="text-base-content">{{ $photo->meta['lens'] }}</dd>
                            </div>
                        @endif

                        @if($photo->meta['iso'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">ISO:</dt>
                                <dd class="text-base-content">{{ $photo->meta['iso'] }}</dd>
                            </div>
                        @endif

                        @if($photo->meta['aperture'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">Aperture:</dt>
                                <dd class="text-base-content">f/{{ $photo->meta['aperture'] }}</dd>
                            </div>
                        @endif

                        @if($photo->meta['shutter_speed'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">Shutter Speed:</dt>
                                <dd class="text-base-content">{{ $photo->meta['shutter_speed'] }}s</dd>
                            </div>
                        @endif

                        @if($photo->meta['focal_length'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">Focal Length:</dt>
                                <dd class="text-base-content">{{ $photo->meta['focal_length'] }}mm</dd>
                            </div>
                        @endif

                        @if(($photo->meta['width'] ?? null) && ($photo->meta['height'] ?? null))
                            <div>
                                <dt class="font-semibold text-base-content/70">Dimensions:</dt>
                                <dd class="text-base-content">{{ $photo->meta['width'] }} × {{ $photo->meta['height'] }} px</dd>
                            </div>
                        @endif

                        @if($photo->meta['created_at'] ?? null)
                            <div>
                                <dt class="font-semibold text-base-content/70">Date Taken (EXIF):</dt>
                                <dd class="text-base-content">{{ $photo->meta['created_at'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        {{-- Footer Meta --}}
        <footer class="mt-8 pt-8 border-t border-base-200">
            {{-- Permalink --}}
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
                <i class="ph ph-link"></i>
                <span>Permalink:</span>
                <a href="{{ route('photos.show', $photo->slug) }}" class="u-url link link-primary">
                    {{ url()->current() }}
                </a>
            </div>

            {{-- Share/Actions --}}
            <div class="flex flex-wrap gap-3 mb-6">
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($photo->alt_text) }}"
                   target="_blank"
                   rel="noopener"
                   class="btn btn-sm btn-outline gap-2">
                    <i class="ph ph-twitter-logo"></i>
                    Share on Twitter
                </a>
                <button onclick="navigator.clipboard.writeText('{{ url()->current() }}'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy Link', 2000)"
                        class="btn btn-sm btn-outline gap-2">
                    <i class="ph ph-copy"></i>
                    Copy Link
                </button>
            </div>

            {{-- Back Link --}}
            <a href="{{ route('photos.index') }}" class="btn btn-ghost gap-2">
                <i class="ph ph-arrow-left"></i>
                Back to Photos
            </a>
        </footer>

        {{-- Hidden metadata for microformats --}}
        <div class="hidden">
            <span class="dt-published">{{ $photo->published_at->toIso8601String() }}</span>
            <span class="p-name">{{ $photo->alt_text }}</span>
            <a class="u-url" href="{{ route('photos.show', $photo->slug) }}">Permalink</a>
        </div>
    </article>

</x-layouts.public>

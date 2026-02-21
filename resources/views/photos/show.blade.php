<x-layouts.public :title="$photo->alt_text . ' - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :photo="$photo" />
    </x-slot:seo>

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
                @if($photo->status === \App\Enums\Status::Draft)
                    <span class="badge badge-warning">Draft</span>
                @endif
            </div>
        @endauth

        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[
            ['label' => 'Photos', 'url' => route('photos.index')],
            ['label' => Str::limit($photo->alt_text, 40)],
        ]" class="mb-6" />

        {{-- Photo --}}
        <figure class="mb-8">
            <img src="{{ $photo->image_url }}"
                 alt="{{ $photo->alt_text }}"
                 class="u-photo w-full h-auto rounded-lg shadow-lg">
        </figure>

        {{-- Caption --}}
        @if($photo->caption)
            <div class="e-content prose prose-lg max-w-none mb-8">
                {!! \App\Support\Markdown::render($photo->caption) !!}
            </div>
        @endif

        {{-- Category Badge --}}
        @if($photo->category)
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ $photo->category->urlFor('photos') }}"
                   class="badge badge-primary badge-outline">
                    {{ $photo->category->name }}
                </a>
            </div>
        @endif

        {{-- Metadata Bar --}}
        <div class="flex flex-wrap items-center gap-4 text-sm text-base-content/60 mb-8 pb-8 border-b border-base-200">
            <div class="flex items-center gap-2">
                <i class="ph ph-calendar-blank"></i>
                <time class="dt-published" datetime="{{ $photo->published_at?->toIso8601String() }}">
                    {{ $photo->published_at?->format('F j, Y') }}
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
                <span class="p-name">{{ $authorName }}</span>
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
                    <x-photo-exif-details :photo="$photo" />
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
            <div class="mb-6">
                <x-share-buttons :url="url()->current()" :text="$photo->alt_text" />
            </div>

            {{-- Back Link --}}
            <a href="{{ route('photos.index') }}" class="btn btn-ghost gap-2">
                <i class="ph ph-arrow-left"></i>
                Back to Photos
            </a>
        </footer>
    </article>

</x-layouts.public>

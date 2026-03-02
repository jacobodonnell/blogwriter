<x-layouts.admin>
    <x-slot:title>Edit Photo</x-slot:title>
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.photos.index') }}">Photos</a></li>
        <li>Edit: {{ $photo->alt_text }}</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{
             originalStatus: '{{ old('status', $photo->status?->value ?? 'private') }}',
             currentStatus: '{{ old('status', $photo->status?->value ?? 'private') }}',
             articleCount: {{ $articleCount }},
             handleSubmit(event) {
                 if (this.originalStatus === 'public' && this.currentStatus === 'private' && this.articleCount > 0) {
                     event.preventDefault();
                     document.getElementById('detach-modal').showModal();
                 }
             },
             confirmDetach() {
                 document.getElementById('detach-modal').close();
                 this.$refs.editForm.submit();
             }
         }">
        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center gap-2">
            <div>
                <h1 class="text-3xl font-bold">Edit Photo</h1>
                <p class="text-base-content/70 mt-1">Update photo details and metadata.</p>
            </div>
            <div class="flex gap-2">
                @if($photo->isPublic())
                    <a href="{{ route('photos.show', $photo->slug) }}"
                       class="btn btn-ghost btn-sm gap-2">
                        <i class="ph ph-arrow-square-out text-lg"></i>
                        View on Site
                    </a>
                @endif
                <a href="{{ route('admin.photos.show', $photo) }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-info text-lg"></i>
                    Details
                </a>
                <a href="{{ route('admin.photos.download', $photo) }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-download-simple text-lg"></i>
                    Download
                </a>
                <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    Back
                </a>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <i class="ph ph-x-circle text-xl"></i>
                <div class="flex flex-col">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST"
              action="{{ route('admin.photos.update', $photo) }}"
              enctype="multipart/form-data"
              class="space-y-6"
              x-ref="editForm"
              @submit="handleSubmit($event)">
            @csrf
            @method('PUT')

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    @include('admin.photos.form')
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-between">
                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary gap-2">
                        <i class="ph ph-check"></i>
                        Update Photo
                    </button>
                    <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost">Cancel</a>
                </div>

                {{-- Delete Button --}}
                <button type="button"
                        onclick="document.getElementById('delete-modal').showModal()"
                        class="btn btn-error btn-outline gap-2">
                    <i class="ph ph-trash"></i>
                    Delete Photo
                </button>
            </div>
        </form>

        {{-- Detach Warning Modal --}}
        <x-editor-modal id="detach-modal" title="Detach Photo from Articles">
            <p>
                Switching this photo to private will remove it as the featured image from
                <span class="font-semibold" x-text="articleCount"></span>
                <span x-text="articleCount === 1 ? 'article' : 'articles'"></span>.
                Private photos are not publicly accessible, so the featured image would appear broken.
            </p>
            <x-slot:actions>
                <button type="button" class="btn btn-warning" @click="confirmDetach()">Switch to Private</button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Delete Confirmation Modal --}}
        <x-editor-modal id="delete-modal" title="Delete Photo">
            <p>
                Are you sure you want to delete this photo?
                @if($articleCount > 0)
                    <span class="text-error font-semibold">
                        This photo will be removed from {{ $articleCount }} {{ Str::plural('article', $articleCount) }} as a featured image.
                    </span>
                @endif
                This action cannot be undone.
            </p>
            <x-slot:actions>
                <form method="POST" action="{{ route('admin.photos.destroy', $photo) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </x-slot:actions>
        </x-editor-modal>
    </div>
</x-layouts.admin>

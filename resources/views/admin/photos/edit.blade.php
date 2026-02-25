<x-layouts.admin>
    <x-slot:title>Edit Photo</x-slot:title>
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.photos.index') }}">Photos</a></li>
        <li>Edit: {{ $photo->alt_text }}</li>
    </x-slot:breadcrumb>

    <div class="space-y-6"
         x-data="{
             originalStatus: '{{ old('status', $photo->status?->value ?? 'draft') }}',
             currentStatus: '{{ old('status', $photo->status?->value ?? 'draft') }}',
             articleCount: {{ $articleCount }},
             handleSubmit(event) {
                 if (this.originalStatus === 'published' && this.currentStatus === 'draft' && this.articleCount > 0) {
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
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Edit Photo</h1>
                <p class="text-base-content/70 mt-1">Update photo details and metadata.</p>
            </div>
            <div class="flex gap-2">
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
        <dialog id="detach-modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Detach Photo from Articles</h3>
                <p class="py-4">
                    Switching this photo to draft will remove it as the featured image from
                    <span class="font-semibold" x-text="articleCount"></span>
                    <span x-text="articleCount === 1 ? 'article' : 'articles'"></span>.
                    Draft photos are not publicly accessible, so the featured image would appear broken.
                </p>
                <div class="modal-action">
                    <button type="button" class="btn btn-warning" @click="confirmDetach()">Switch to Draft</button>
                    <form method="dialog">
                        <button class="btn">Cancel</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>

        {{-- Delete Confirmation Modal --}}
        <dialog id="delete-modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Delete Photo</h3>
                <p class="py-4">
                    Are you sure you want to delete this photo?
                    @if($articleCount > 0)
                        <span class="text-error font-semibold">
                            This photo is currently used in {{ $articleCount }} {{ Str::plural('article', $articleCount) }}.
                        </span>
                    @endif
                    This action cannot be undone.
                </p>
                <div class="modal-action">
                    <form method="POST" action="{{ route('admin.photos.destroy', $photo) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">Delete</button>
                    </form>
                    <form method="dialog">
                        <button class="btn">Cancel</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </div>
</x-layouts.admin>

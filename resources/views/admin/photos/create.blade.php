<x-layouts.admin>
    <x-slot:title>New Photo</x-slot:title>
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.photos.index') }}">Photos</a></li>
        <li>New Photo</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">New Photo</h1>
                <p class="text-base-content/70 mt-1">Upload a new photo to your library.</p>
            </div>
            <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-2">
                <i class="ph ph-arrow-left text-lg"></i>
                Back
            </a>
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
              action="{{ route('admin.photos.store') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    @include('admin.photos.form')
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary gap-2">
                    <i class="ph ph-check"></i>
                    Create Photo
                </button>
                <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>

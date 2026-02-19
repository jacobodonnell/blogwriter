<x-layouts.admin>
    <x-slot:title>Photo: {{ $photo->alt_text }}</x-slot:title>
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.photos.index') }}">Photos</a></li>
        <li>{{ $photo->alt_text }}</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ $photo->alt_text }}</h1>
                <p class="text-base-content/70 mt-1">Photo details</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.photos.edit', $photo) }}" class="btn btn-primary btn-sm gap-2">
                    <i class="ph ph-pencil-simple text-lg"></i>
                    Edit
                </a>
                <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-2">
                    <i class="ph ph-arrow-left text-lg"></i>
                    Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Image Preview --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    @if($photo->image_url)
                        <figure>
                            <img src="{{ $photo->image_url }}"
                                 alt="{{ $photo->alt_text }}"
                                 class="w-full rounded-lg">
                        </figure>
                    @else
                        <div class="flex items-center justify-center h-64 bg-base-200 rounded-lg">
                            <span class="text-base-content/50">No image uploaded</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body space-y-4">
                    <h3 class="card-title">Details</h3>

                    <div class="overflow-x-auto">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="w-1/3">Status</th>
                                    <td>
                                        <span class="badge {{ $photo->status->value === 'published' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($photo->status->value) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Slug</th>
                                    <td><code>{{ $photo->slug }}</code></td>
                                </tr>
                                <tr>
                                    <th>Filename</th>
                                    <td>{{ $photo->filename }}</td>
                                </tr>
                                @if($photo->caption)
                                <tr>
                                    <th>Caption</th>
                                    <td>{{ $photo->caption }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Alt Text</th>
                                    <td>{{ $photo->alt_text }}</td>
                                </tr>
                                @if($photo->published_at)
                                <tr>
                                    <th>Published</th>
                                    <td>{{ $photo->published_at->format('M j, Y g:ia') }}</td>
                                </tr>
                                @endif
                                @if($photo->taken_at)
                                <tr>
                                    <th>Taken At</th>
                                    <td>{{ $photo->taken_at->format('M j, Y g:ia') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Created</th>
                                    <td>{{ $photo->created_at->format('M j, Y g:ia') }}</td>
                                </tr>
                                <tr>
                                    <th>Used By</th>
                                    <td>{{ $photo->articles()->count() }} {{ Str::plural('article', $photo->articles()->count()) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>

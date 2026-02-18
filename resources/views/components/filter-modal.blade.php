@props(['id' => 'filter-modal', 'title' => 'Filters', 'action', 'clearRoute'])

<dialog id="{{ $id }}" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box sm:max-w-2xl">
        {{-- Header with close button --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg">{{ $title }}</h3>
            <form method="dialog">
                <button class="btn btn-ghost btn-sm btn-square">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </form>
        </div>

        {{-- GET form wrapping the filter fields --}}
        <form method="GET" action="{{ $action }}" {{ $attributes }}>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                {{ $slot }}
            </div>

            <div class="modal-action">
                @if($clearRoute && (request('search') || request('category') || request('status')))
                    <a href="{{ $clearRoute }}"
                       @click.prevent="search = ''; category = ''; status = ''; window.location = '{{ $clearRoute }}';"
                       class="btn btn-ghost">Clear</a>
                @endif
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@props(['action', 'target', 'clearRoute', 'persistKey' => '', 'activeFilterCount' => 0])

<div x-show="filtersOpen" x-collapse x-cloak class="card bg-base-100 shadow">
    <div class="card-body">
        <form method="GET" action="{{ $action }}"
              x-target="{{ $target }}"
              class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto_auto] gap-4 items-end">

            {{-- Hidden fields slot (for sort/direction preservation) --}}
            @if(isset($hidden))
                {{ $hidden }}
            @endif

            {{ $slot }}

            @if($activeFilterCount > 0)
                <a href="{{ $clearRoute }}" class="btn btn-ghost">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>
</div>

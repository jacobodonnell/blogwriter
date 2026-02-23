<div x-data="{
    open: {{ $persistKey ? "\$persist(" . ($defaultOpen ? 'true' : 'false') . ").as('" . $persistKey . "')" : ($defaultOpen ? 'true' : 'false') }},
    hasFilters: {{ $activeFilterCount > 0 ? 'true' : 'false' }},
    checkFilters() {
        this.hasFilters = [...this.$refs.filterForm.querySelectorAll('select, input:not([type=hidden])')].some(el => el.value !== '');
    },
    clearFilters() {
        this.$refs.filterForm.querySelectorAll('select').forEach(s => {
            const def = s.dataset.clearValue;
            s.value = def !== undefined ? def : '';
        });
        this.$refs.filterForm.querySelectorAll('input:not([type=hidden])').forEach(i => i.value = '');
        this.hasFilters = false;
    },
}" class="mb-6">
    {{-- Always-visible navigation --}}
    @if(isset($navigation) && trim((string) $navigation) !== '')
        <div class="mb-3">
            {{ $navigation }}
        </div>
    @endif

    {{-- Toolbar row: Filter Toggle + Clear + toolbar slot --}}
    <div class="flex items-center gap-2">
        <button @click="open = !open" class="btn btn-ghost btn-sm gap-1" type="button">
            <i class="ph ph-funnel text-lg"></i>
            Filters
            @if($activeFilterCount > 0)
                <span class="badge badge-xs badge-primary">{{ $activeFilterCount }}</span>
            @endif
            <i class="ph ph-caret-down text-sm transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>
        <a x-show="hasFilters" x-cloak
           href="{{ $clearRoute }}" x-target.push="{{ $target }}"
           @ajax:after="clearFilters()"
           data-test="filter-clear"
           class="btn btn-ghost btn-sm gap-1">
            <i class="ph ph-x text-sm"></i>
            Clear
        </a>

        {{-- Right-aligned toolbar slot --}}
        @if(isset($toolbar))
            <div class="ml-auto flex items-center gap-2">
                {{ $toolbar }}
            </div>
        @endif
    </div>

    {{-- Collapsible filter form --}}
    <div x-show="open" x-collapse x-cloak class="mt-3">
        <form x-ref="filterForm" method="GET" action="{{ $action }}"
              x-target.push="{{ $target }}"
              @change="checkFilters()" @input="checkFilters()"
              @submit="$el.querySelectorAll('[name]').forEach(el => { if (!el.value) el.removeAttribute('name') })"
              class="card bg-base-100 shadow-sm card-body p-4 gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                {{ $slot }}
            </div>
        </form>
    </div>
</div>

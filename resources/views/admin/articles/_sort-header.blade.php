@php
    $isActive = $currentSort === $column;
    $nextDirection = ($isActive && $currentDirection === 'asc') ? 'desc' : 'asc';
@endphp
<a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection]) }}"
   x-target="articles-table"
   class="flex items-center gap-1 hover:text-primary {{ $isActive ? 'text-primary font-semibold' : '' }}">
    {{ $label }}
    @if($isActive)
        <i class="ph ph-caret-{{ $currentDirection === 'asc' ? 'up' : 'down' }}"></i>
    @endif
</a>

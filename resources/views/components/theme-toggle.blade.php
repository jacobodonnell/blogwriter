@props(['size' => ''])

@php
    $iconSize = $size === 'sm' ? 'text-lg' : 'text-xl';
@endphp

<button @click="cycleTheme()" class="btn btn-ghost btn-circle {{ $size === 'sm' ? 'btn-sm' : '' }}" aria-label="Cycle theme mode">
    <i x-show="themeMode === 'light'" class="ph ph-sun {{ $iconSize }}" x-cloak></i>
    <i x-show="themeMode === 'dark'" class="ph ph-moon {{ $iconSize }}" x-cloak></i>
    <i x-show="themeMode === 'system'" class="ph ph-monitor {{ $iconSize }}" x-cloak></i>
</button>

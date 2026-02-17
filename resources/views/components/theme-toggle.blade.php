@props(['size' => ''])

<button @click="cycleTheme()" class="btn btn-ghost btn-circle {{ $size === 'sm' ? 'btn-sm' : '' }}" aria-label="Cycle theme mode">
    <i x-show="themeMode === 'light'" class="ph ph-sun {{ $size === 'sm' ? 'text-lg' : 'text-xl' }}" x-cloak></i>
    <i x-show="themeMode === 'dark'" class="ph ph-moon {{ $size === 'sm' ? 'text-lg' : 'text-xl' }}" x-cloak></i>
    <i x-show="themeMode === 'system'" class="ph ph-monitor {{ $size === 'sm' ? 'text-lg' : 'text-xl' }}" x-cloak></i>
</button>

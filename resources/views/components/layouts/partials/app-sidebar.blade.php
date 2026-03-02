{{-- Shared sidebar for authenticated layouts --}}
{{-- Expects Alpine.js context with: expanded, mobileDrawerOpen, isDesktop, closeMobile() --}}

{{-- Mobile backdrop --}}
<div x-show="mobileDrawerOpen && !isDesktop"
     x-transition:enter="transition-opacity duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="closeMobile()"
     class="fixed inset-0 bg-black/50 z-30 lg:hidden"
     x-cloak>
</div>

{{-- Sidebar --}}
<aside class="fixed lg:sticky top-0 lg:top-16 h-screen lg:h-[calc(100vh-4rem)] z-40 lg:z-20 bg-base-100 border-r border-base-300 flex flex-col transition-all duration-300"
       :class="{
           'w-64': expanded && isDesktop,
           'w-20': !expanded && isDesktop,
           'w-64 translate-x-0': mobileDrawerOpen && !isDesktop,
           '-translate-x-full': !mobileDrawerOpen && !isDesktop
       }"
       @keydown.escape.window="closeMobile()">

    {{-- Mobile close button --}}
    <div class="flex items-center justify-end h-16 px-4 border-b border-base-300 shrink-0 lg:hidden">
        <button @click="closeMobile()" class="btn btn-ghost btn-circle" aria-label="Close sidebar">
            <i class="ph ph-x text-lg"></i>
        </button>
    </div>

    {{-- Navigation (scrollable inner area) --}}
    <nav class="flex-1 py-4 px-2 overflow-y-auto overflow-x-visible">
        @include('components.layouts.partials.navigation-menu')
    </nav>

</aside>

{{-- Fixed tooltip for collapsed sidebar --}}
<div x-ref="sidebarTooltip"
     x-show="tooltipText"
     x-text="tooltipText"
     x-cloak
     class="fixed z-50 px-2 py-1 text-sm rounded bg-neutral text-neutral-content shadow-lg pointer-events-none whitespace-nowrap"
     :style="`top: ${tooltipY}px; left: ${tooltipX}px;`">
</div>

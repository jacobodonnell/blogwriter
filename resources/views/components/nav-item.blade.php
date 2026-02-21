@props(['href', 'icon', 'label', 'active' => false])

<li>
    <a href="{{ $href }}"
       class="{{ $active ? 'menu-active' : '' }}"
       :class="!expanded && isDesktop && 'justify-center'"
       @mouseenter="showTooltip($event, '{{ $label }}')"
       @mouseleave="hideTooltip()">
        <i class="ph ph-{{ $icon }} text-lg"></i>
        <span
            class="whitespace-nowrap overflow-hidden transition-opacity"
            :class="(expanded || !isDesktop) ? 'opacity-100 duration-150' : 'opacity-0 duration-75 w-0 pointer-events-none'"
            :style="(expanded && isDesktop) ? 'transition-delay: 250ms' : 'transition-delay: 0ms'"
        >{{ $label }}</span>
    </a>
</li>

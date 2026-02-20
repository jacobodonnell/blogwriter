@props(['tooltip', 'href', 'icon'])

<div class="tooltip" data-tip="{{ $tooltip }}">
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-sm btn-ghost']) }}>
        <i class="ph ph-{{ $icon }} text-lg"></i>
    </a>
</div>

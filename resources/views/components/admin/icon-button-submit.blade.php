@props(['tooltip', 'icon'])

<div class="tooltip" data-tip="{{ $tooltip }}">
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-ghost']) }}>
        <i class="ph ph-{{ $icon }} text-lg"></i>
    </button>
</div>

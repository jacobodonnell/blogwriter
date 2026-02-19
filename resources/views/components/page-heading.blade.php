@props(['title', 'subtitle' => null, 'large' => false])

<h1 {{ $attributes->merge(['class' => ($large ? 'text-4xl md:text-5xl' : 'text-3xl') . ' font-bold leading-tight']) }}>
    {{ $title }}
</h1>
@if($subtitle)
    <p class="text-base-content/60">{{ $subtitle }}</p>
@endif

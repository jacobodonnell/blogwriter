@props(['url'])

<button onclick="navigator.clipboard.writeText('{{ $url }}'); this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy Link', 2000)"
        {{ $attributes->merge(['class' => 'btn btn-sm btn-outline gap-2']) }}>
    <i class="ph ph-copy"></i>
    Copy Link
</button>

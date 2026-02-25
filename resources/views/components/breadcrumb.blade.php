@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'text-sm breadcrumbs mb-4 overflow-visible']) }}>
    <ul>
        @if($slot->isNotEmpty())
            <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
            {{ $slot }}
        @elseif(count($items) > 0)
            <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
            @foreach($items as $item)
                @if(!empty($item['url']))
                    <li><a href="{{ $item['url'] }}" class="link link-hover">{{ $item['label'] }}</a></li>
                @else
                    <li class="text-base-content/60 whitespace-normal">{{ $item['label'] }}</li>
                @endif
            @endforeach
        @else
            <li class="text-base-content/60">Home</li>
        @endif
    </ul>
</nav>

@props(['title', 'maxWidth' => 'max-w-lg'])

<dialog {{ $attributes->merge(['class' => 'modal']) }}>
    <div class="modal-box {{ $maxWidth }}">
        <h3 class="font-bold text-lg">{{ $title }}</h3>
        <div class="py-4">
            {{ $slot }}
        </div>
        @if(isset($actions))
            <div class="modal-action">
                {{ $actions }}
                <form method="dialog">
                    <button class="btn">Cancel</button>
                </form>
            </div>
        @endif
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

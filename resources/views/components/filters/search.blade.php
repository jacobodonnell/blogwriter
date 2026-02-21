@props(['label' => 'Search', 'placeholder' => 'Search...', 'name' => 'search', 'auth' => false, 'colspan' => 1])

@if($auth)
    @auth
        <div @class([
            'sm:col-span-2' => $colspan == 2,
            'sm:col-span-3' => $colspan == 3,
            'sm:col-span-4' => $colspan == 4,
        ])>
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <input type="text" name="{{ $name }}" value="{{ request($name) }}"
                   placeholder="{{ $placeholder }}"
                   {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
                   @input.debounce.400ms="$el.form.requestSubmit()">
        </div>
    @endauth
@else
    <div @class([
        'sm:col-span-2' => $colspan == 2,
        'sm:col-span-3' => $colspan == 3,
        'sm:col-span-4' => $colspan == 4,
    ])>
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <input type="text" name="{{ $name }}" value="{{ request($name) }}"
               placeholder="{{ $placeholder }}"
               {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
               @input.debounce.400ms="$el.form.requestSubmit()">
    </div>
@endif

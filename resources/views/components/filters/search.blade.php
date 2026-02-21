@props(['label' => 'Search', 'placeholder' => 'Search...', 'name' => 'search', 'auth' => false, 'colspan' => 1])

@php $wrapper = function($content) use ($auth) { return $auth ? '@auth' . $content . '@endauth' : $content; }; @endphp

@if($auth)
    @auth
        <div class="sm:col-span-{{ $colspan }}">
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <input type="text" name="{{ $name }}" value="{{ request($name) }}"
                   placeholder="{{ $placeholder }}"
                   {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
                   @input.debounce.400ms="$el.form.requestSubmit()">
        </div>
    @endauth
@else
    <div class="sm:col-span-{{ $colspan }}">
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <input type="text" name="{{ $name }}" value="{{ request($name) }}"
               placeholder="{{ $placeholder }}"
               {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
               @input.debounce.400ms="$el.form.requestSubmit()">
    </div>
@endif

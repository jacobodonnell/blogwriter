<div @class([$colspanClass])>
    <label class="label"><span class="label-text">{{ $label }}</span></label>
    <input type="text" name="{{ $name }}" value="{{ request($name) }}"
           placeholder="{{ $placeholder }}"
           data-test="filter-search"
           {{ $attributes->merge(['class' => 'input input-bordered w-full']) }}
           @input.debounce.400ms="$el.form.requestSubmit()">
</div>

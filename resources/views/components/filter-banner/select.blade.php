<div @class([$colspanClass])>
    <label class="label"><span class="label-text">{{ $label }}</span></label>
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
            @change="$el.form.requestSubmit()">
        <option value="">{{ $emptyLabel }}</option>
        @foreach($options as $value => $display)
            <option value="{{ $value }}" @selected((request($name) ?: $default) === (string) $value)>{{ $display }}</option>
        @endforeach
    </select>
</div>

<div @class([$colspanClass])>
    <label class="label"><span class="label-text">{{ $label }}</span></label>
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
            @change="$el.form.requestSubmit()">
        @foreach($options as $value => $display)
            <option value="{{ $value }}" @selected(request($name) === (string) $value)>{{ $display }}</option>
        @endforeach
    </select>
</div>

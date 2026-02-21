<div @class([$colspanClass])>
    <label class="label"><span class="label-text">{{ $label }}</span></label>
    <select name="perPage" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
            @change="$el.form.requestSubmit()">
        @foreach($options as $option)
            <option value="{{ $option }}" @selected((int) request('perPage', $default) === $option)>{{ $option }}</option>
        @endforeach
    </select>
</div>

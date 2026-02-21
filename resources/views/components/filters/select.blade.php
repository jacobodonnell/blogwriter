@props(['name', 'label', 'options' => [], 'emptyLabel' => 'All', 'auth' => false, 'colspan' => 1])

@if($auth)
    @auth
        <div @class([
            'sm:col-span-2' => $colspan == 2,
            'sm:col-span-3' => $colspan == 3,
            'sm:col-span-4' => $colspan == 4,
        ])>
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
                    @change="$el.form.requestSubmit()">
                <option value="">{{ $emptyLabel }}</option>
                @foreach($options as $value => $display)
                    <option value="{{ $value }}" @selected(request($name) === (string) $value)>{{ $display }}</option>
                @endforeach
            </select>
        </div>
    @endauth
@else
    <div @class([
        'sm:col-span-2' => $colspan == 2,
        'sm:col-span-3' => $colspan == 3,
        'sm:col-span-4' => $colspan == 4,
    ])>
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
                @change="$el.form.requestSubmit()">
            <option value="">{{ $emptyLabel }}</option>
            @foreach($options as $value => $display)
                <option value="{{ $value }}" @selected(request($name) === (string) $value)>{{ $display }}</option>
            @endforeach
        </select>
    </div>
@endif

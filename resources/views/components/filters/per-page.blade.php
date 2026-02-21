@props([
    'label' => 'Per Page',
    'options' => [10, 20, 50, 100],
    'default' => 20,
    'auth' => false,
    'colspan' => 1,
])

@if($auth)
    @auth
        <div class="sm:col-span-{{ $colspan }}">
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <select name="perPage" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
                    @change="$el.form.requestSubmit()">
                @foreach($options as $option)
                    <option value="{{ $option }}" @selected((int) request('perPage', $default) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    @endauth
@else
    <div class="sm:col-span-{{ $colspan }}">
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <select name="perPage" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
                @change="$el.form.requestSubmit()">
            @foreach($options as $option)
                <option value="{{ $option }}" @selected((int) request('perPage', $default) === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
@endif

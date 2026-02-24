@props(['field', 'method' => null])

<button type="button"
        x-show="isDraftField('{{ $field }}')"
        @click="{{ $method ?? "revertField('" . $field . "')" }}"
        x-cloak
        class="btn btn-ghost btn-xs tooltip tooltip-bottom"
        data-test="revert-{{ $field }}"
        data-tip="Revert to saved"
        aria-label="Revert {{ $field }} to saved value">
    <i class="ph ph-arrow-counter-clockwise"></i>
</button>

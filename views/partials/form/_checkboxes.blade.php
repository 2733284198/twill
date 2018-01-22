@php
    $note = $note ?? false;
    $options = method_exists($options, 'map') ? $options->map(function($label, $value) {
        return [
            'value' => $value,
            'label' => $label
        ];
    })->values()->toArray() : $options;
    $inline = $inline ?? false;
@endphp

<a17-checkboxgroup
    label="{{ $label }}"
    @include('cms-toolkit::partials.form.utils._field_name')
    :options='{!! json_encode($options) !!}'
    :inline='{{ $inline ? 'true' : 'false' }}'
    @if ($min ?? false) :min="{{ $min }}" @endif
    @if ($max ?? false) :max="{{ $max }}" @endif
    @if ($note) note='{{ $note }}' @endif
    in-store="currentValue"
></a17-checkboxgroup>

@unless($renderForBlocks || $renderForModal)
@push('vuexStore')
    window.STORE.form.fields.push({
        name: '{{ $name }}',
        value: {!! json_encode(array_pluck($item->$name, 'id')) !!}
    })
@endpush
@endunless

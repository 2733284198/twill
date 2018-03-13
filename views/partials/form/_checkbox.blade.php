@php
    $note = $note ?? false;
    $inline = $inline ?? false;
    $default = $default ?? false;
@endphp

<a17-singlecheckbox
    @include('cms-toolkit::partials.form.utils._field_name')
    label="{{ $label ?? '' }}"
    :initial-value="{{ $default ? 'true' : 'false' }}"
    @if ($note) note='{{ $note }}' @endif
    :has-default-store="true"
    in-store="currentValue"
></a17-singlecheckbox>

@unless($renderForBlocks || $renderForModal || (!isset($item->$name) && null == $formFieldsValue = getFormFieldsValue($form_fields, $name)))
@push('vuexStore')
    window.STORE.form.fields.push({
        name: '{{ $name }}',
        value: @if(isset($item) && $item->$name || ($formFieldsValue ?? false)) true @else false @endif
    })
@endpush
@endunless

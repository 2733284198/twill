@php
    $max = $max ?? 1;
@endphp

<a17-inputframe label="{{ $label }}">
    @if($max > 1)
        <a17-slideshow
            name="{{ $name }}"
            :max="{{ $max ?? 1 }}"
            crop-context="{{ $name }}"
            @if ($required ?? false) required @endif
        >{{ $note or '' }}</a17-slideshow>
    @else
        <a17-mediafield
            name="{{ $name }}"
            crop-context="{{ $name }}"
            @if ($required ?? false) required @endif
        >{{ $note or '' }}</a17-mediafield>
    @endif
</a17-inputframe>

@push('fieldsStore')
    @if (isset($form_fields['medias']))
        window.STORE.medias.selected["{{ $name }}"] = {!! json_encode($form_fields['medias'][$name]) !!}
    @endif
@endpush

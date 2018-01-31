@php
    $max = $max ?? 1;
    $required = $required ?? false;
    $note = $note ?? '';
    $withAddInfo = $withAddInfo ?? true;
    $withVideoUrl = $withVideoUrl ?? true;
@endphp

<a17-inputframe label="{{ $label }}" name="medias.{{ $name }}" @if ($required) :required="true" @endif>
    @if($max > 1)
        <a17-slideshow
            @include('cms-toolkit::partials.form.utils._field_name')
            :max="{{ $max }}"
            crop-context="{{ $name }}"
            @if ($required) :required="true" @endif
            @if (!$withAddInfo) :with-add-info="false" @endif
            @if (!$withVideoUrl) :with-video-url="false" @endif
        >{{ $note }}</a17-slideshow>
    @else
        <a17-mediafield
            @include('cms-toolkit::partials.form.utils._field_name')
            crop-context="{{ $name }}"
            @if ($required) :required="true" @endif
            @if (!$withAddInfo) :with-add-info="false" @endif
            @if (!$withVideoUrl) :with-video-url="false" @endif
        >{{ $note }}</a17-mediafield>
    @endif
</a17-inputframe>

@unless($renderForBlocks)
@push('vuexStore')
    @if (isset($form_fields['medias']) && isset($form_fields['medias'][$name]))
        window.STORE.medias.selected["{{ $name }}"] = {!! json_encode($form_fields['medias'][$name]) !!}
    @endif
@endpush
@endunless

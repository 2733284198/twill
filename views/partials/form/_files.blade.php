@php
    $max = $max ?? 1;
    $noTranslate = $noTranslate ?? false;
    $note = $note ?? 'Add' . ($max > 1 ? " up to $max files" : ' one file');
    $itemLabel = $itemLabel ?? strtolower($label);
@endphp

@if($noTranslate)
    <a17-filefield
        name="{{ $name }}"
        label="{{ $label }}"
        item-label="{{ $itemLabel }}"
        note="{{ $note }}"
        :max="{{ $max }}"
    ></a17-filefield>
@else
    <a17-locale
        type="a17-filefield"
        :attributes="{
            label: '{{ $label }}',
            itemLabel: '{{ $itemLabel }}',
            name: '{{ $name }}',
            note: '{{ $note }}',
            max: {{ $max }}
        }"
    ></a17-locale>
@endif

@push('fieldsStore')
    @if ($noTranslate)
        @if (isset($item->$name))
            window.STORE.medias.selected["{{ $name }}"] = {!! json_encode($item->$name) !!}
        @endif
    @else
        @foreach(getLocales() as $locale)
            @if (isset($item->$name[$locale]))
                window.STORE.medias.selected["{{ $name }}['{{ $locale }}']"] = {!! json_encode($item->$name[$locale]) !!}
            @endif
        @endforeach
    @endif
@endpush

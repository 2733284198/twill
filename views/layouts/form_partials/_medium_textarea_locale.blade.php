@php
    $required = $required ?? "";
    $rows = $rows ?? 10;
    $options = [];

    if (isset($textLimit)) {
        $options['maxlength'] = "{$textLimit}";
    }
@endphp

@foreach (getLocales() as $locale)
    @php
        if (isset($field_wrapper)) {
            $fullField = $field_wrapper . '[' . $field . '_' . $locale . ']';
            $fieldValue = $form_fields[$fullField] ?? (isset($item) && $item->$field_wrapper ? $item->$field_wrapper->getTranslation($locale)[$field] : null);
        } else {
            $fullField = $field . '.' . $locale;
        }
    @endphp
    <div class="input text {{ $required }} {{ $fullField }} field_with_lang" data-lang="{{$locale}}" >
        <label class="string {{ $required }} control-label" for="{{ $fullField }}">
            {{ $field_name }}  {!! !empty($required) ? '<abbr title="required">*</abbr>' : '' !!}
            <span class="lang_tag" data-behavior="lang_toggle">{{strtoupper($locale)}}</span>
            {!! isset($hint) ? '<div class="/hint"> '.$hint.'</div>' : '' !!}
        </label>
        {!! Form::textarea($fullField, $fieldValue ?? null,[
            'class' => "textarea-medium-editor string {$required}",
            'id' => $fullField,
            'rows' => $rows,
            'data-behavior' => "medium_editor",
            'data-medium-editor-js' => "assets/admin/vendor/medium-editor/medium-editor.min.js",
            'data-medium-editor-css' => "assets/admin/vendor/medium-editor/medium-editor.css, assets/admin/vendor/medium-editor/themes/flat.min.css",
            'data-medium-editor-options' => $data_medium_editor_options ?? '',
        ] + $options) !!}
        @if (isset($textLimit))
            <span class="hint"><span class="textlimit-remaining">0</span> / {{ $textLimit }} characters maximum</span>
        @endif
    </div>
@endforeach

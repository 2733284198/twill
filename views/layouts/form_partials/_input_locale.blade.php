@php
    $required = $required ?? "";
    $options = [];
    $behavior = "";
    $options['placeholder'] = '';

    if (isset($textLimit)) {
        $options['maxlength'] = "{$textLimit}";
        $behavior = "textlimit";
    }

    if (isset($placeholder)) {
        $options['placeholder'] = $placeholder;
    }

    if (isset($readonly)) {
        $options['readonly'] = $readonly;
    }

    if (isset($disabled)) {
        $options['disabled'] = $disabled;
    }
@endphp

@foreach (getLocales() as $locale)
    @php
        if (isset($field_wrapper)) {
            $fullField = $field_wrapper . '[' . $field . '_' . $locale . ']';
            $fieldValue = $form_fields[$fullField] ?? (isset($item) && $item->$field_wrapper ? $item->$field_wrapper->getTranslation($locale)[$field] : null);
            $fieldId = $fullField;
        } else {
            $fullField = $field . '.' . $locale;
            $fieldValue = $form_fields[$fullField] ?? null;
            $fieldId = $field . '_' . $locale;
        }

        if (isset($repeater) && $repeater) {
            $fullField = $moduleName . '[' . $repeaterIndex . '][' . $fullField . ']';
            $fieldValue = $form_fields[$moduleName][$repeaterIndex][$field . '_' . $locale] ?? null;
            $fieldId = $moduleName . '[' . $repeaterIndex . '][' . $fieldId . ']';

        }
    @endphp
    <div class="input string {{ $fullField }} field_with_hint field_with_lang" data-lang="{{ $locale }}">
        <label class="string control-label" for="{{ $fullField }}" data-behavior="{{ $behavior }}">
            {!! $field_name !!} {!! !empty($required) ? '<abbr title="required">*</abbr>' : '' !!}
            @unless($loop->first && $loop->last)
                <span class="lang_tag" data-behavior="lang_toggle">{{ strtoupper($locale) }}</span>
            @endunless
            {!! isset($hint) ? '<span class="hint">'.$hint.'</span>' : '' !!}
        </label>
        {!! Form::text($fullField, $fieldValue ?? null, ['class' => "string {$fullField}", 'id' => $fieldId] + $options) !!}
        @if (isset($textLimit))
            <span class="hint"><span class="textlimit-remaining">0</span> / {{ $textLimit }} characters maximum</span>
        @endif
    </div>
@endforeach

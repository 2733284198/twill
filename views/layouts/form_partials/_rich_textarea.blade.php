@php
    $options = $options ?? [];
    if (isset($textLimit)) {
        $options['maxlength'] = "{$textLimit}";
    }
@endphp

<div class="input text {{$field}}">
    <label class="string control-label" for="{{$field}}">
        {!!$field_name!!} {!! !empty($required) ? '<abbr title="required">*</abbr>' : '' !!}
    </label>
    {!! Form::textarea($field, null,[
        'class' => "textarea-medium-editor string",
        'id' => $field,
        'data-behavior' => "markitup"] + $options) !!}
    @if (isset($textLimit))
        <span class="hint"><span class="textlimit-remaining">0</span> / {{ $textLimit }} characters maximum</span>
    @endif
</div>

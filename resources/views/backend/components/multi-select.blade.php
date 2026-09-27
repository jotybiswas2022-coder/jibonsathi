@props([
    'name',
    'label',
    'options' => [],
    'selected' => [],
    'hint' => null,
    'class' => null,
])

@php
    /* Values arrive from `old()` as strings, from the model as whatever the
       column cast gave back, so both sides are compared as strings. */
    $chosen = array_map('strval', (array) $selected);

    /* Reference lists come in both shapes: religions are keyed by the value the
       column stores, hobbies are a plain list where the value is the label. Both
       are flattened to value => label here so the caller can pass either. */
    $isList = array_is_list($options);
    $items = [];
    foreach ($options as $key => $text) {
        $items[$isList ? $text : $key] = $text;
    }
@endphp

<fieldset class="ms {{ $class }}" data-ms>
    <legend class="ms-legend">{{ $label }}</legend>

    <div class="ms-tools">
        <span class="ms-count" data-ms-count>{{ count($chosen) }} selected</span>
        <button type="button" class="ms-clear" data-ms-clear hidden>Clear</button>
    </div>

    <div class="ms-list">
        @foreach ($items as $value => $text)
            <label class="ms-chip">
                <input type="checkbox" name="{{ $name }}" value="{{ $value }}"
                    @checked(in_array((string) $value, $chosen, true))>
                <span class="ms-chip-text">{{ $text }}</span>
            </label>
        @endforeach
    </div>

    @if ($hint)
        <span class="hint ms-hint">{{ $hint }}</span>
    @endif
</fieldset>

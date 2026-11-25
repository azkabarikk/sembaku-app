@props([
    'kind', 'name' => null, 'id' => null, 'label' => null, 'value' => null, 'placeholder' => null,
    'required' => false, 'disabled' => false, 'readonly' => false, 'helper' => null, 'error' => null,
    'type' => 'text', 'options' => null, 'checked' => false, 'multiple' => false, 'useOld' => true,
    'leadingIcon' => null,
])

@php
    $choice = in_array($kind, ['checkbox', 'radio', 'switch'], true);
    $switch = $kind === 'switch';
    $id ??= 'md3-'.\Illuminate\Support\Str::uuid();
    $oldKey = $name === null ? null : rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $error ??= $oldKey === null ? null : (($errors ?? null)?->first($oldKey) ?: null);
    $formSession = $useOld && request()->hasSession() ? request()->session() : null;
    $submitted = $oldKey !== null && $formSession?->has('_old_input');
    $current = $useOld && $oldKey !== null && ! in_array($type, ['password', 'file'], true) ? old($oldKey, $value) : $value;
    if ($kind === 'select' && $multiple && $submitted) {
        $current = old($oldKey, []);
    }
    if ($kind === 'select' && ! $multiple && $submitted && \Illuminate\Support\Arr::has($formSession->getOldInput(), $oldKey)) {
        $current ??= '';
    }
    $choiceValue = $value ?? '1';
    $oldChoice = $submitted ? old($oldKey) : null;
    $isChecked = $choice && ($submitted
        ? (is_array($oldChoice) ? in_array((string) $choiceValue, array_map('strval', array_filter($oldChoice, 'is_scalar')), true)
            : is_scalar($oldChoice) && (string) $oldChoice === (string) $choiceValue)
        : $checked);
    $selectedValues = array_map('strval', array_filter((array) $current, 'is_scalar'));
    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'), $helper !== null ? $id.'-helper' : null, $error !== null ? $id.'-error' : null,
    ])));
    $control = $attributes->except(['aria-describedby', 'aria-invalid'])->merge([
        'id' => $id,
        'name' => $name,
        'aria-describedby' => $describedBy ?: null,
        'aria-invalid' => $error !== null ? 'true' : $attributes->get('aria-invalid'),
    ])->class($choice
        ? ['md3-choice__input', 'md3-choice__input--switch' => $switch]
        : ['md3-control', 'md3-control--select' => $kind === 'select', 'md3-control--error' => $error !== null]);
@endphp

<div class="md3-field">
    @if ($choice)
        <label class="md3-choice" for="{{ $id }}">
            <input {{ $control }} type="{{ $kind === 'radio' ? 'radio' : 'checkbox' }}" value="{{ $choiceValue }}"
                @if ($switch) role="switch" @endif
                @checked($isChecked) @required($required) @disabled($disabled)>
            @if ($switch)
                <span class="md3-choice__track" aria-hidden="true"></span>
            @endif
            <span>{{ $label }}</span>
        </label>
    @else
        @if ($label !== null)
            <label class="md3-field__label" for="{{ $id }}">{{ $label }}</label>
        @endif
        @if ($kind === 'textarea')
            <textarea {{ $control }} @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif @required($required) @disabled($disabled) @readonly($readonly)>{{ is_scalar($current) ? $current : '' }}</textarea>
        @elseif ($kind === 'select')
            <select {{ $control }} @required($required) @disabled($disabled) @if ($multiple) multiple @endif
                @if ($options === null && ($current !== null || ($submitted && $multiple)))
                    data-md3-select-value="{{ json_encode($selectedValues, JSON_INVALID_UTF8_SUBSTITUTE) }}"
                @endif
            >
                {{ $slot }}
                @foreach ($options ?? [] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selectedValues, true))>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @else
            <div @class(['md3-field__input', 'md3-field__input--leading' => $leadingIcon !== null, 'md3-field__input--trailing' => isset($trailing)])>
                @if ($leadingIcon !== null)
                    <x-ui.icon :name="$leadingIcon" class="md3-field__leading" />
                @endif
                <input {{ $control }} type="{{ $type }}" @if ($type !== 'file' && is_scalar($current)) value="{{ $current }}" @endif
                    @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif @required($required) @disabled($disabled) @readonly($readonly)>
                @isset($trailing)
                    <div class="md3-field__trailing">{{ $trailing }}</div>
                @endisset
            </div>
        @endif
    @endif

    @if ($helper !== null)
        <span id="{{ $id }}-helper" class="md3-field__helper">{{ $helper }}</span>
    @endif
    @if ($error !== null)
        <span id="{{ $id }}-error" class="md3-field__error">{{ $error }}</span>
    @endif
</div>

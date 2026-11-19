@props(['value' => null, 'label' => 'Memuat', 'variant' => 'neutral'])

@php
    $value = is_numeric($value) ? max(0, min(100, (float) $value)) : null;
    $variant = in_array($variant, ['success', 'warning', 'error', 'info', 'neutral'], true) ? $variant : 'neutral';
@endphp

<div {{ $attributes->class(['md3-progress', 'md3-tone--'.$variant])->merge(['role' => 'progressbar', 'aria-label' => $label, 'aria-valuemin' => 0, 'aria-valuemax' => 100, 'aria-valuenow' => $value]) }}>
    <div class="md3-progress__track" @if ($value === null) data-indeterminate @endif>
        <span @if ($value !== null) style="--md3-progress-value: {{ $value }}%;" @endif></span>
    </div>
</div>

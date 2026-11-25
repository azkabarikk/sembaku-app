@props(['variant' => 'filled', 'size' => 'medium', 'type' => 'button', 'disabled' => false, 'loading' => false, 'fullWidth' => false, 'href' => null, 'iconName' => null])

@php
    $variant = in_array($variant, ['filled', 'elevated', 'tonal', 'outlined', 'text'], true) ? $variant : 'filled';
    $size = in_array($size, ['small', 'medium', 'large'], true) ? $size : 'medium';
    $classes = [
        'md3-action md3-button md3-button--'.$variant.' md3-button--'.$size,
        'md3-full-width' => $fullWidth,
    ];
@endphp

@if ($href !== null)
    <a {{ $attributes->class($classes) }}
        @if ($disabled || $loading) aria-disabled="true" @else href="{{ $href }}" @endif
        @if ($loading) aria-busy="true" @endif
    >
@else
    <button {{ $attributes->class($classes) }} type="{{ $type }}" @disabled($disabled || $loading)
        @if ($loading) aria-busy="true" @endif
    >
@endif
    @if ($loading)
        <span class="md3-spinner" aria-hidden="true"></span>
    @elseif (isset($icon))
        <span class="md3-button__icon">{{ $icon }}</span>
    @elseif ($iconName !== null)
        <span class="md3-button__icon"><x-ui.icon :name="$iconName" /></span>
    @endif
    {{ $slot }}
@if ($href !== null)
    </a>
@else
    </button>
@endif

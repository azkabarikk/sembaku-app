@props(['variant' => 'neutral', 'selected' => null, 'interactive' => false, 'disabled' => false, 'type' => 'button'])

@php($variant = in_array($variant, ['success', 'warning', 'error', 'info', 'neutral'], true) ? $variant : 'neutral')

@if ($interactive)
    <button {{ $attributes->class(['md3-action md3-chip', 'md3-tone--'.$variant]) }} type="{{ $type }}"
        @if ($selected !== null) aria-pressed="{{ $selected ? 'true' : 'false' }}" @endif @disabled($disabled)>
        @isset($icon) {{ $icon }} @endisset
        {{ $slot }}
    </button>
@else
    <span {{ $attributes->class(['md3-chip', 'md3-tone--'.$variant]) }}>
        @isset($icon) {{ $icon }} @endisset
        {{ $slot }}
    </span>
@endif

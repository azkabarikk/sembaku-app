@props(['variant' => 'neutral'])

@php($variant = in_array($variant, ['success', 'warning', 'error', 'info', 'neutral'], true) ? $variant : 'neutral')

<span {{ $attributes->class(['md3-badge', 'md3-tone--'.$variant]) }}>{{ $slot }}</span>

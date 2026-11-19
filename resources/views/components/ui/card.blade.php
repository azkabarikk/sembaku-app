@props(['variant' => 'filled'])

@php($variant = in_array($variant, ['filled', 'elevated', 'outlined'], true) ? $variant : 'filled')

<div {{ $attributes->class(['md3-card', 'md3-card--'.$variant]) }}>
    @isset($header)
        <div class="md3-card__header">{{ $header }}</div>
    @endisset
    {{ $slot }}
    @isset($footer)
        <div class="md3-card__footer">{{ $footer }}</div>
    @endisset
</div>

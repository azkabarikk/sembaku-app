@props(['variant' => 'info', 'title' => null])

@php
    $variant = in_array($variant, ['success', 'warning', 'error', 'info', 'neutral'], true) ? $variant : 'info';
    $iconName = match ($variant) {
        'success' => 'check',
        'warning', 'error' => 'warning',
        default => 'info',
    };
@endphp

<div {{ $attributes->class(['md3-alert', 'md3-tone--'.$variant])->merge([
    'role' => $variant === 'error' ? 'alert' : 'status',
    'tabindex' => $variant === 'error' ? '-1' : null,
]) }}>
    <span class="md3-alert__icon">
        @isset($icon)
            {{ $icon }}
        @else
            <x-ui.icon :name="$iconName" />
        @endisset
    </span>
    <div class="md3-alert__content">
        @if ($title !== null)
            <strong>{{ $title }}</strong>
        @endif
        {{ $slot }}
    </div>
</div>

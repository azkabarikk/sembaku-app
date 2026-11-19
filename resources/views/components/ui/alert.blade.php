@props(['variant' => 'info', 'title' => null])

@php($variant = in_array($variant, ['success', 'warning', 'error', 'info', 'neutral'], true) ? $variant : 'info')

<div {{ $attributes->class(['md3-alert', 'md3-tone--'.$variant])->merge(['role' => $variant === 'error' ? 'alert' : 'status']) }}>
    @isset($icon)
        <span class="md3-alert__icon">{{ $icon }}</span>
    @endisset
    <div class="md3-alert__content">
        @if ($title !== null)
            <strong>{{ $title }}</strong>
        @endif
        {{ $slot }}
    </div>
</div>

@props([ 'variant' => 'neutral', 'dismissible' => true])

@php($variant = in_array($variant, ['success', 'warning', 'error', 'info', 'neutral'], true) ? $variant : 'neutral')

<div {{ $attributes->class(['md3-snackbar', 'md3-tone--'.$variant])->merge(['data-md3-snackbar' => '']) }}>
    <span role="{{ $variant === 'error' ? 'alert' : 'status' }}">{{ $slot }}</span>
    @isset($actions)
        <div class="md3-cluster">{{ $actions }}</div>
    @endisset
    @if ($dismissible)
        <x-ui.icon-button label="Tutup pesan" icon="close" data-md3-snackbar-dismiss />
    @endif
</div>

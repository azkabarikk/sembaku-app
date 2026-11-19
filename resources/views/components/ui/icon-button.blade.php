@props(['label', 'icon' => null, 'variant' => 'text'])

<x-ui.button :variant="$variant" {{ $attributes->class(['md3-icon-button'])->merge(['aria-label' => $label]) }}>
    @if ($icon !== null)
        <x-ui.icon :name="$icon" />
    @else
        {{ $slot }}
    @endif
</x-ui.button>

@props(['title', 'value', 'icon' => null, 'variant' => 'elevated'])

<x-ui.card :variant="$variant" {{ $attributes->class(['md3-stat-card']) }}>
    <div class="md3-stat-card__inner">
        <dl>
            <dt class="md3-type-label-large md3-muted">{{ $title }}</dt>
            <dd class="md3-type-headline-medium">{{ $value }}</dd>
        </dl>
        @if ($icon !== null)
            <span class="md3-stat-card__icon"><x-ui.icon :name="$icon" /></span>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="md3-type-body-small md3-muted">{{ $slot }}</div>
    @endif
</x-ui.card>

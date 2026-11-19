@props(['title', 'description' => null, 'icon' => null])

<div {{ $attributes->class(['md3-empty-state']) }}>
    @if ($icon !== null)
        <span class="md3-empty-state__icon"><x-ui.icon :name="$icon" /></span>
    @endif
    <h3 class="md3-type-title-large">{{ $title }}</h3>
    @if ($description !== null)
        <p class="md3-muted">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div>{{ $slot }}</div>
    @endif
</div>

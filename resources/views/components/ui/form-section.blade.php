@props(['title', 'description' => null, 'icon' => null])

<fieldset {{ $attributes->class(['md3-form-section']) }}>
    <legend class="md3-type-title-medium">
        @if ($icon !== null)
            <span class="md3-form-section__icon"><x-ui.icon :name="$icon" /></span>
        @endif
        <span>{{ $title }}</span>
    </legend>
    @if ($description !== null)
        <p class="md3-form-section__description md3-muted">{{ $description }}</p>
    @endif
    <div class="md3-stack">{{ $slot }}</div>
</fieldset>

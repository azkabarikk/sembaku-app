@props(['title', 'description' => null])

<fieldset {{ $attributes->class(['md3-form-section']) }}>
    <legend class="md3-type-title-medium">{{ $title }}</legend>
    @if ($description !== null)
        <p class="md3-form-section__description md3-muted">{{ $description }}</p>
    @endif
    <div class="md3-stack">{{ $slot }}</div>
</fieldset>

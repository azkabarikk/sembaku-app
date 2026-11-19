@props(['id', 'title', 'description' => null])

<dialog {{ $attributes->class(['md3-dialog'])->merge([
    'id' => $id,
    'aria-labelledby' => $id.'-title',
    'aria-describedby' => $description !== null ? $id.'-description' : null,
    'data-md3-dialog' => '',
]) }}>
    <div class="md3-dialog__content md3-stack">
        <h2 id="{{ $id }}-title" class="md3-type-headline-small">{{ $title }}</h2>
        @if ($description !== null)
            <p id="{{ $id }}-description">{{ $description }}</p>
        @endif
        {{ $slot }}
        <div class="md3-dialog__actions md3-cluster">
            @if (isset($actions) && $actions->isNotEmpty())
                {{ $actions }}
            @else
                <x-ui.button variant="text" data-md3-dialog-close autofocus>Tutup</x-ui.button>
            @endif
        </div>
    </div>
</dialog>

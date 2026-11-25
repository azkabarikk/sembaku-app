@props(['cancel', 'submit' => 'Simpan', 'cancelLabel' => 'Batal'])

<div {{ $attributes->class(['md3-form-actions']) }}>
    <x-ui.button variant="outlined" :href="$cancel">{{ $cancelLabel }}</x-ui.button>
    <x-ui.button icon-name="check" type="submit">{{ $submit }}</x-ui.button>
</div>

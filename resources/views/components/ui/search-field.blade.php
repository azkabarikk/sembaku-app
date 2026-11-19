@props(['label' => 'Cari'])

<div class="md3-search-field">
    <x-ui.text-field type="search" :$label {{ $attributes->except('type') }} />
</div>

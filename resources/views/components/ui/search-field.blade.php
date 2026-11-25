@props(['label' => 'Cari', 'leadingIcon' => 'search'])

<div class="md3-search-field">
    <x-ui.text-field type="search" :$label :$leadingIcon {{ $attributes->except('type') }} />
</div>

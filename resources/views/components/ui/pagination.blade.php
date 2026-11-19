@props(['label' => 'Paginasi'])

<div {{ $attributes->class(['md3-pagination'])->merge(['role' => 'group', 'aria-label' => $label]) }}>
    {{ $slot }}
</div>

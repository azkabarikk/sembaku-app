@props(['title', 'subtitle' => null, 'heading' => 'h2'])

<x-ui.page-header :$title :$subtitle :$heading {{ $attributes->class(['md3-section-header']) }}>
    {{ $slot }}
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-ui.page-header>

@props(['label', 'filters' => [], 'paginator' => null, 'server' => false, 'numbered' => false])

<x-ui.data-list :$label :$filters :$paginator :$server :$numbered>
    <x-ui.table :$label {{ $attributes }}>
        <x-slot:head>{{ $head }}</x-slot:head>
        {{ $slot }}
    </x-ui.table>
</x-ui.data-list>

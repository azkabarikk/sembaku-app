@props(['label' => 'Tabel data', 'caption' => null, 'compact' => false])

<div class="md3-table-scroll" role="region" aria-label="{{ $label }}" tabindex="0">
    <table {{ $attributes->class(['md3-table', 'md3-table--compact' => $compact]) }}>
        @if ($caption !== null || $compact)
            <caption @class(['md3-table__scroll-hint' => $caption === null])>
                {{ $caption ?? 'Geser tabel untuk melihat kolom lainnya.' }}
            </caption>
        @endif
        @isset($head)
            <thead>{{ $head }}</thead>
        @endisset
        <tbody>{{ $slot }}</tbody>
        @isset($foot)
            <tfoot>{{ $foot }}</tfoot>
        @endisset
    </table>
</div>

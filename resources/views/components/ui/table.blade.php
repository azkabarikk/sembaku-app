@props(['label' => 'Tabel data', 'caption' => null])

<div class="md3-table-scroll" role="region" aria-label="{{ $label }}" tabindex="0">
    <table {{ $attributes->class(['md3-table']) }}>
        @if ($caption !== null)
            <caption>{{ $caption }}</caption>
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

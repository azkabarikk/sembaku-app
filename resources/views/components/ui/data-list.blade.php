@props(['label', 'filters' => [], 'emptyId' => null, 'paginator' => null, 'server' => false, 'numbered' => false])

<div class="md3-stack md3-data-list" data-list data-page="{{ $paginator?->currentPage() ?? 1 }}"
    @if ($server) data-server data-per-page="{{ $paginator->perPage() }}" @endif
    @if ($numbered) data-numbered @endif>
    <div class="md3-data-toolbar" data-toolbar hidden>
        @isset($search)
            {{ $search }}
        @else
            <x-ui.search-field :label="$server ? 'Cari di halaman ini' : 'Cari di '.$label" data-search autocomplete="off" />
        @endisset
        @foreach ($filters as $filter => $column)
            @if ($filter === 'Tanggal')
                <x-ui.text-field type="date" :label="$filter" data-column="{{ $column }}" />
            @else
                <x-ui.select :label="$filter" data-column="{{ $column }}">
                    <option value="">Semua</option>
                </x-ui.select>
            @endif
        @endforeach
        @if ($server)
            <span class="md3-muted">{{ $paginator->perPage() }} baris per halaman</span>
        @else
            <x-ui.select label="Data per halaman" data-size>
                @foreach ([10, 20, 50] as $size)
                    <option value="{{ $size }}" @selected($size === ($paginator?->perPage() ?? 10))>{{ $size }}</option>
                @endforeach
            </x-ui.select>
        @endif
    </div>

    {{ $slot }}

    <p class="md3-muted" role="status" @if ($emptyId) id="{{ $emptyId }}" @endif data-empty hidden>Tidak ada hasil yang sesuai.</p>
    <div class="md3-data-footer">
        <span class="md3-muted md3-type-label-large" data-range aria-live="polite"></span>
        <x-ui.pagination :label="'Halaman '.$label" data-pager :hidden="! $paginator?->hasPages()">
            @if ($paginator)
                <a class="md3-action md3-button md3-button--outlined md3-button--small" data-prev
                    @if ($paginator->previousPageUrl()) href="{{ $paginator->previousPageUrl() }}" @else aria-disabled="true" @endif>Sebelumnya</a>
            @else
                <x-ui.button data-prev variant="outlined" size="small">Sebelumnya</x-ui.button>
            @endif
            @if ($numbered)
                <div class="md3-data-page-numbers" data-page-numbers></div>
            @endif
            <span data-page-label @if ($numbered) class="visually-hidden" aria-live="polite" @else aria-current="page" @endif>Halaman {{ $paginator?->currentPage() ?? 1 }}</span>
            @if ($paginator)
                <a class="md3-action md3-button md3-button--outlined md3-button--small" data-next
                    @if ($paginator->nextPageUrl()) href="{{ $paginator->nextPageUrl() }}" @else aria-disabled="true" @endif>Berikutnya</a>
            @else
                <x-ui.button data-next variant="outlined" size="small">Berikutnya</x-ui.button>
            @endif
        </x-ui.pagination>
    </div>
</div>

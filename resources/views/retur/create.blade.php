@extends('layouts.app')

@section('title', 'Tambah Retur')
@section('heading', 'Tambah Retur')
@section('description', 'Pilih barang dari penerimaan dan isi jumlah yang dikembalikan.')

@section('content')
    @if ($errors->any())
        <x-ui.alert variant="error">
            <strong>Retur belum tersimpan.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <dl class="md3-record">
        <dt>Penerimaan</dt><dd>#{{ $penerimaan->idpenerimaan }}</dd>
        <dt>Pengadaan</dt><dd>#{{ $penerimaan->idpengadaan }}</dd>
        <dt>Vendor</dt><dd>{{ $penerimaan->nama_vendor ?? '—' }}</dd>
        <dt>Tanggal</dt><dd>{{ $penerimaan->created_at }}</dd>
    </dl>

    <form class="md3-stack" method="POST" action="{{ route('retur.store') }}" id="retur-form">
        @csrf
        <input type="hidden" name="idpenerimaan" value="{{ $penerimaan->idpenerimaan }}">

        <x-ui.table label="Barang yang dapat diretur">
            <x-slot:head>
                <tr>
                    <th scope="col">Pilih</th>
                    <th scope="col">Barang</th>
                    <th scope="col">Diterima</th>
                    <th scope="col">Sudah Diretur</th>
                    <th scope="col">Sisa</th>
                    <th scope="col">Jumlah Retur</th>
                    <th scope="col">Alasan</th>
                </tr>
            </x-slot:head>
            @forelse ($rincian as $baris)
                @if ($baris->sisa_retur > 0)
                    @php($dipilih = old('items.'.$loop->index.'.iddetail_penerimaan') == $baris->iddetail_penerimaan)
                    <tr data-retur-row>
                        <td>
                            <input type="checkbox" class="md3-choice__input" data-retur-toggle
                                aria-label="Pilih {{ $baris->nama_barang }}" @checked($dipilih)>
                            <input type="hidden" name="items[{{ $loop->index }}][iddetail_penerimaan]"
                                value="{{ $baris->iddetail_penerimaan }}" @disabled(! $dipilih)>
                        </td>
                        <td><strong>{{ $baris->nama_barang }}</strong><span class="d-block md3-muted">{{ $baris->nama_satuan }}</span></td>
                        <td>{{ $baris->jumlah_terima }}</td>
                        <td>{{ $baris->jumlah_diretur }}</td>
                        <td>{{ $baris->sisa_retur }}</td>
                        <td>
                            <input class="md3-control" type="number" min="1" max="{{ $baris->sisa_retur }}"
                                name="items[{{ $loop->index }}][jumlah]" value="{{ old('items.'.$loop->index.'.jumlah') }}"
                                aria-label="Jumlah retur {{ $baris->nama_barang }}" @disabled(! $dipilih) required>
                        </td>
                        <td>
                            <input class="md3-control" type="text" maxlength="200"
                                name="items[{{ $loop->index }}][alasan]" value="{{ old('items.'.$loop->index.'.alasan') }}"
                                aria-label="Alasan retur {{ $baris->nama_barang }}" @disabled(! $dipilih) required>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="7">Tidak ada barang yang tersisa untuk diretur.</td></tr>
            @endforelse
        </x-ui.table>

        <div class="md3-cluster">
            <x-ui.button type="submit">Simpan Retur</x-ui.button>
            <x-ui.button variant="tonal" :href="route('retur.index')">Batal</x-ui.button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-retur-row]').forEach((row) => {
            const toggle = row.querySelector('[data-retur-toggle]');
            toggle.addEventListener('change', () => {
                row.querySelectorAll('[name]').forEach((field) => {
                    field.disabled = !toggle.checked;
                });
            });
        });
    </script>
@endpush

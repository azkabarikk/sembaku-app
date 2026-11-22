@extends('layouts.app')

@section('title', 'Tambah Penerimaan')
@section('heading', 'Penerimaan Barang')
@section('description', 'Catat barang yang diterima dari pengadaan.')

@section('content')
    @if ($errors->has('jumlah_terima'))
        <x-ui.alert variant="error">{{ $errors->first('jumlah_terima') }}</x-ui.alert>
    @endif
    @error('penerimaan')
        <x-ui.alert variant="error">{{ $message }}</x-ui.alert>
    @enderror

    <div class="md3-card md3-card--outlined mb-4">
        <div>
            <div><strong>Pengadaan:</strong> #{{ $pengadaan->idpengadaan }}</div>
            <div><strong>Vendor:</strong> {{ $pengadaan->nama_vendor ?? '—' }}</div>
            <div><strong>Tanggal:</strong> {{ $pengadaan->dibuat }}</div>
        </div>
    </div>

    <form class="md3-stack" method="POST" action="{{ route('penerimaan.store', $pengadaan->idpengadaan) }}">
        @csrf
        <input type="hidden" name="penerimaan_terakhir" value="{{ $idTerakhir }}">

        <p class="md3-muted mb-2">Kosongkan harga untuk memakai harga yang tercatat pada pengadaan.</p>

        <x-ui.table label="Penerimaan Barang">
            <x-slot:head>
                <tr>
                    <th scope="col">Barang</th>
                    <th scope="col">Dipesan</th>
                    <th scope="col">Sudah Diterima</th>
                    <th scope="col">Draf Lain</th>
                    <th scope="col">Sisa</th>
                    <th scope="col">Diterima Sekarang</th>
                    <th scope="col">Harga Satuan Terima (Rp)</th>
                </tr>
            </x-slot:head>
            @foreach ($barisPesanan as $baris)
                <tr>
                    <td>{{ $baris->nama_barang }}</td>
                    <td>{{ $baris->jumlah_dipesan }}</td>
                    <td>{{ $baris->jumlah_diterima }}</td>
                    <td>{{ $baris->jumlah_draf }}</td>
                    <td>{{ $baris->jumlah_sisa }}</td>
                    <td>
                        <input
                            class="md3-control @error('jumlah_terima.' . $baris->idbarang) md3-control--error @enderror"
                            name="jumlah_terima[{{ $baris->idbarang }}]"
                            aria-label="Jumlah diterima {{ $baris->nama_barang }}"
                            type="number"
                            min="0"
                            max="{{ $baris->jumlah_sisa }}"
                            value="{{ old('jumlah_terima.' . $baris->idbarang, 0) }}"
                            required
                        >
                        @error('jumlah_terima.' . $baris->idbarang)
                            <div class="md3-field__error">{{ $message }}</div>
                        @enderror
                    </td>
                    <td>
                        <input
                            class="md3-control @error('harga_satuan_terima.' . $baris->idbarang) md3-control--error @enderror"
                            name="harga_satuan_terima[{{ $baris->idbarang }}]"
                            aria-label="Harga satuan {{ $baris->nama_barang }}"
                            type="number"
                            min="0"
                            max="2147483647"
                            step="1"
                            placeholder="{{ $baris->harga_pesanan }}"
                            value="{{ old('harga_satuan_terima.' . $baris->idbarang) }}"
                        >
                        @error('harga_satuan_terima.' . $baris->idbarang)
                            <div class="md3-field__error">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <div class="md3-cluster">
            <x-ui.button variant="filled" type="submit">Simpan Penerimaan</x-ui.button>
            <x-ui.button variant="tonal" :href="route('penerimaan.index')">Batal</x-ui.button>
        </div>
    </form>
@endsection

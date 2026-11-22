@extends('layouts.app')

@section('title', 'Edit Penerimaan')
@section('heading', 'Edit Penerimaan')
@section('description', 'Periksa jumlah dan harga. Menyimpan draf akan menambah stok dan mengunci penerimaan.')

@section('content')
    @if ($errors->has('penerimaan'))
        <x-ui.alert variant="error">{{ $errors->first('penerimaan') }}</x-ui.alert>
    @endif
    @if ($errors->has('jumlah_terima'))
        <x-ui.alert variant="error">{{ $errors->first('jumlah_terima') }}</x-ui.alert>
    @endif

    <div class="md3-card md3-card--outlined mb-4">
        <div>
            <div><strong>Penerimaan:</strong> #{{ $penerimaan->idpenerimaan }}</div>
            <div><strong>Pengadaan:</strong> #{{ $penerimaan->idpengadaan }}</div>
            <div><strong>Tanggal:</strong> {{ $penerimaan->created_at }}</div>
        </div>
    </div>

    <form class="md3-stack" method="POST" action="{{ route('penerimaan.update', $penerimaan->idpenerimaan) }}">
        @csrf
        @method('PUT')

        <x-ui.table label="Edit Penerimaan">
            <x-slot:head>
                <tr>
                    <th scope="col">Barang</th>
                    <th scope="col">Dipesan</th>
                    <th scope="col">Diterima di Penerimaan Lain</th>
                    <th scope="col">Draf Lain</th>
                    <th scope="col">Batas untuk Draf Ini</th>
                    <th scope="col">Jumlah Diterima</th>
                    <th scope="col">Harga Satuan Terima (Rp)</th>
                </tr>
            </x-slot:head>
            @forelse ($barisPesanan as $baris)
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
                            value="{{ old('jumlah_terima.' . $baris->idbarang, $rincianDraf[$baris->idbarang]->jumlah_terima ?? 0) }}"
                            required
                        >
                        @error('jumlah_terima.' . $baris->idbarang)
                            <div class="md3-field__error">{{ $message }}</div>
                        @enderror
                    </td>
                    <td>
                        <input
                            type="number"
                            class="md3-control @error('harga_satuan_terima.' . $baris->idbarang) md3-control--error @enderror"
                            name="harga_satuan_terima[{{ $baris->idbarang }}]"
                            aria-label="Harga satuan {{ $baris->nama_barang }}"
                            min="0"
                            max="2147483647"
                            step="1"
                            placeholder="{{ $baris->harga_pesanan }}"
                            value="{{ old('harga_satuan_terima.' . $baris->idbarang, $rincianDraf[$baris->idbarang]->harga_satuan_terima ?? null) }}"
                        >
                        @error('harga_satuan_terima.' . $baris->idbarang)
                            <div class="md3-field__error">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada barang yang masih dapat diterima.</td>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="md3-cluster">
            <x-ui.button variant="filled" type="submit">Simpan Penerimaan</x-ui.button>
            <x-ui.button variant="tonal" :href="route('penerimaan.index')">Kembali</x-ui.button>
        </div>
    </form>
@endsection

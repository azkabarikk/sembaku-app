@extends('layouts.app')

@section('title', 'Detail Retur')
@section('heading', 'Detail Retur #'.$retur->idretur)

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    <dl class="md3-record">
        <dt>Tanggal</dt><dd>{{ $retur->created_at }}</dd>
        <dt>Penerimaan</dt><dd>#{{ $retur->idpenerimaan }}</dd>
        <dt>Pengadaan</dt><dd>#{{ $retur->idpengadaan }}</dd>
        <dt>Vendor</dt><dd>{{ $retur->nama_vendor ?? '—' }}</dd>
        <dt>Petugas</dt><dd>{{ $retur->username ?? '—' }}</dd>
    </dl>

    <x-ui.data-table label="Detail Retur">
        <x-slot:head>
            <tr>
                <th scope="col">Barang</th>
                <th scope="col">Satuan</th>
                <th scope="col">Jumlah</th>
                <th scope="col">Alasan</th>
            </tr>
        </x-slot:head>
        @foreach ($rincian as $baris)
            <tr>
                <td>{{ $baris->nama_barang ?? '—' }}</td>
                <td>{{ $baris->nama_satuan ?? '—' }}</td>
                <td>{{ $baris->jumlah }}</td>
                <td>{{ $baris->alasan }}</td>
            </tr>
        @endforeach
    </x-ui.data-table>

    <x-ui.button variant="tonal" :href="route('retur.index')">Kembali ke Retur</x-ui.button>
@endsection

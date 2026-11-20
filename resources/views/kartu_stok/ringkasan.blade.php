@extends('layouts.app')

@section('title', 'Stok Barang')
@section('heading', 'Stok Barang')

@section('content')
    <x-ui.table label="Stok Barang">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Barang</th>
                <th scope="col">Satuan</th>
                <th scope="col">Total Masuk</th>
                <th scope="col">Total Keluar</th>
                <th scope="col">Saldo Akhir</th>
            </tr>
        </x-slot:head>
        @forelse ($stok as $barang)
            <tr>
                <td>{{ $barang->idbarang }}</td>
                <td>{{ $barang->nama }}</td>
                <td>{{ $barang->nama_satuan }}</td>
                <td>{{ $barang->total_masuk }}</td>
                <td>{{ $barang->total_keluar }}</td>
                <td>
                    {{ $barang->saldo }}
                    @if ((int) $barang->saldo !== (int) $barang->saldo_kartu)
                        <span class="d-block md3-field__error">Saldo kartu: {{ $barang->saldo_kartu }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">Belum ada barang.</td></tr>
        @endforelse
    </x-ui.table>
@endsection

@extends('layouts.app')

@section('title', 'Stok Barang')
@section('heading', 'Stok Barang')

@section('content')
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Barang</th>
                    <th scope="col">Satuan</th>
                    <th scope="col">Total Masuk</th>
                    <th scope="col">Total Keluar</th>
                    <th scope="col">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
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
                                <span class="d-block text-danger">Saldo kartu: {{ $barang->saldo_kartu }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Belum ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

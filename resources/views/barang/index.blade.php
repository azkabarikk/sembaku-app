@extends('layouts.app')

@section('title', 'Barang')
@section('heading', 'Daftar Barang')
@section('description', 'Data barang persediaan.')

@section('content')
    <p>Jumlah data: <strong>{{ count($daftarBarang) }}</strong></p>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Satuan</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarBarang as $barang)
                    <tr>
                        <td>{{ $barang->idbarang }}</td>
                        <td>{{ $barang->nama }}</td>
                        <td>{{ $barang->jenis === 'S' ? 'Sembako' : 'Rumah Tangga' }}</td>
                        <td>{{ $barang->nama_satuan }}</td>
                        <td>Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                        <td>
                            @if ($barang->status == 1)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

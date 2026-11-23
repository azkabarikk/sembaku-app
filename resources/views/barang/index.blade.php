@extends('layouts.app')

@section('title', 'Barang')
@section('heading', 'Daftar Barang')
@section('description', 'Data barang persediaan.')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarBarang) }}</strong></p>
        <a class="btn btn-primary" href="{{ route('barang.create') }}">Tambah Barang</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Satuan</th>
                    <th scope="col">Harga Barang</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
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
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('barang.edit', $barang->idbarang) }}">Edit</a>

                            @if ($barang->status == 1)
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('barang.destroy', $barang->idbarang) }}"
                                    onsubmit="return confirm('Nonaktifkan barang ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Nonaktifkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Vendor')
@section('heading', 'Daftar Vendor')
@section('description', 'Data vendor pemasok.')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarVendor) }}</strong></p>
        <a class="btn btn-primary" href="{{ route('vendor.create') }}">Tambah Vendor</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Vendor</th>
                    <th scope="col">Badan Hukum</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarVendor as $vendor)
                    <tr>
                        <td>{{ $vendor->idvendor }}</td>
                        <td>{{ $vendor->nama_vendor }}</td>
                        <td>{{ $vendor->badan_hukum === 'B' ? 'Berbadan Hukum' : 'Tidak Berbadan Hukum' }}</td>
                        <td>
                            @if ($vendor->status === 'A')
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('vendor.edit', $vendor->idvendor) }}">Edit</a>

                            @if ($vendor->status === 'A')
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('vendor.destroy', $vendor->idvendor) }}"
                                    onsubmit="return confirm('Nonaktifkan vendor ini?')"
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
                        <td colspan="5" class="text-center">Belum ada data vendor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

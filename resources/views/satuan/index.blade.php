@extends('layouts.app')

@section('title', 'Satuan')
@section('heading', 'Daftar Satuan')
@section('description', 'Data satuan barang.')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarSatuan) }}</strong></p>
        <a class="btn btn-primary" href="{{ route('satuan.create') }}">Tambah Satuan</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Satuan</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarSatuan as $satuan)
                    <tr>
                        <td>{{ $satuan->idsatuan }}</td>
                        <td>{{ $satuan->nama_satuan }}</td>
                        <td>
                            @if ($satuan->status == 1)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('satuan.edit', $satuan->idsatuan) }}">Edit</a>

                            @if ($satuan->status == 1)
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('satuan.destroy', $satuan->idsatuan) }}"
                                    onsubmit="return confirm('Nonaktifkan satuan ini?')"
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
                        <td colspan="4" class="text-center">Belum ada data satuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

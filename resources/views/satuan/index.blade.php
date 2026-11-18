@extends('layouts.app')

@section('title', 'Satuan')
@section('heading', 'Daftar Satuan')
@section('description', 'Data satuan barang.')

@section('content')
    <p>Jumlah data: <strong>{{ count($daftarSatuan) }}</strong></p>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Satuan</th>
                    <th scope="col">Status</th>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data satuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

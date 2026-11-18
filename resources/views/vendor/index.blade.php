@extends('layouts.app')

@section('title', 'Vendor')
@section('heading', 'Daftar Vendor')
@section('description', 'Data vendor pemasok.')

@section('content')
    <p>Jumlah data: <strong>{{ count($daftarVendor) }}</strong></p>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Vendor</th>
                    <th scope="col">Badan Hukum</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarVendor as $vendor)
                    <tr>
                        <td>{{ $vendor->idvendor }}</td>
                        <td>{{ $vendor->nama_vendor }}</td>
                        <td>{{ $vendor->badan_hukum }}</td>
                        <td>{{ $vendor->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data vendor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

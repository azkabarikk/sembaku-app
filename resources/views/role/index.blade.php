@extends('layouts.app')

@section('title', 'Role')
@section('heading', 'Daftar Role')
@section('description', 'Data role pengguna.')

@section('content')
    <p>Jumlah data: <strong>{{ count($daftarRole) }}</strong></p>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarRole as $role)
                    <tr>
                        <td>{{ $role->idrole }}</td>
                        <td>{{ $role->nama_role }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Belum ada data role.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

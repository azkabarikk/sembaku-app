@extends('layouts.app')

@section('title', 'User')
@section('heading', 'Daftar User')
@section('description', 'Data akun dan role pengguna.')

@section('content')
    <p>Jumlah data: <strong>{{ count($daftarUser) }}</strong></p>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarUser as $user)
                    <tr>
                        <td>{{ $user->iduser }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->nama_role }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

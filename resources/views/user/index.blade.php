@extends('layouts.app')

@section('title', 'User')
@section('heading', 'Daftar User')
@section('description', 'Data akun dan role pengguna.')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarUser) }}</strong></p>
        <a class="btn btn-primary" href="{{ route('user.create') }}">Tambah User</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Role</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarUser as $user)
                    <tr>
                        <td>{{ $user->iduser }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->nama_role }}</td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('user.edit', $user->iduser) }}">Edit</a>
                            <form
                                class="d-inline"
                                method="POST"
                                action="{{ route('user.destroy', $user->iduser) }}"
                                onsubmit="return confirm('Hapus user ini?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

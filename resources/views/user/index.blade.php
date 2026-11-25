@extends('layouts.app')

@section('title', 'User')
@section('heading', 'Daftar User')
@section('description', 'Data akun dan role pengguna.')

@section('page-actions')
    <x-ui.button variant="filled" :href="route('user.create')">
        <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
        Tambah User
    </x-ui.button>
@endsection

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    @if (session('error'))
        <x-ui.snackbar variant="error">{{ session('error') }}</x-ui.snackbar>
    @endif

    <x-ui.data-table label="Daftar User" :filters="['Role' => 2]">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Username</th>
                <th scope="col">Role</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarUser as $user)
            <tr>
                <td>{{ $user->iduser }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->nama_role }}</td>
                <td class="text-nowrap">
                    <x-ui.button icon-name="edit" variant="outlined" size="small" :href="route('user.edit', $user->iduser)">Edit</x-ui.button>
                    <form
                        class="d-inline"
                        method="POST"
                        action="{{ route('user.destroy', $user->iduser) }}"
                        data-md3-confirm="Hapus akun “{{ $user->username }}”? Akun yang terhubung dengan transaksi tidak dapat dihapus."
                        data-md3-confirm-action="Hapus"
                        onsubmit="return confirm('Hapus user ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <x-ui.button icon-name="trash" variant="outlined" size="small" class="md3-action--error" type="submit">Hapus</x-ui.button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Belum ada data user.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

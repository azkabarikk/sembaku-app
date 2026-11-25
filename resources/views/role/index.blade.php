@extends('layouts.app')

@section('title', 'Role')
@section('heading', 'Daftar Role')
@section('description', 'Data role pengguna.')

@section('page-actions')
    <x-ui.button variant="filled" :href="route('role.create')">
        <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
        Tambah Role
    </x-ui.button>
@endsection

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    @if (session('error'))
        <x-ui.snackbar variant="error">{{ session('error') }}</x-ui.snackbar>
    @endif

    <x-ui.data-table label="Daftar Role">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nama Role</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarRole as $role)
            <tr>
                <td>{{ $role->idrole }}</td>
                <td>{{ $role->nama_role }}</td>
                <td class="text-nowrap">
                    <x-ui.button icon-name="edit" variant="outlined" size="small" :href="route('role.edit', $role->idrole)">Edit</x-ui.button>
                    <form
                        class="d-inline"
                        method="POST"
                        action="{{ route('role.destroy', $role->idrole) }}"
                        data-md3-confirm="Hapus role “{{ $role->nama_role }}”? Role yang masih digunakan tidak dapat dihapus."
                        data-md3-confirm-action="Hapus"
                        onsubmit="return confirm('Hapus role ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <x-ui.button icon-name="trash" variant="outlined" size="small" class="md3-action--error" type="submit">Hapus</x-ui.button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">Belum ada data role.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

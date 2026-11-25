@extends('layouts.app')

@section('title', 'Vendor')
@section('heading', 'Daftar Vendor')
@section('description', 'Data vendor pemasok.')

@section('page-actions')
    <x-ui.button variant="filled" :href="route('vendor.create')">
        <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
        Tambah Vendor
    </x-ui.button>
@endsection

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    <x-ui.data-table label="Daftar Vendor" :filters="['Badan hukum' => 2, 'Status' => 3]">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nama Vendor</th>
                <th scope="col">Badan Hukum</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarVendor as $vendor)
            <tr>
                <td>{{ $vendor->idvendor }}</td>
                <td>{{ $vendor->nama_vendor }}</td>
                <td>{{ $vendor->badan_hukum === 'B' ? 'Berbadan Hukum' : 'Tidak Berbadan Hukum' }}</td>
                <td>
                    @if ($vendor->status === 'A')
                        <x-ui.chip variant="success">Aktif</x-ui.chip>
                    @else
                        <x-ui.chip variant="neutral">Tidak Aktif</x-ui.chip>
                    @endif
                </td>
                <td class="text-nowrap">
                    <x-ui.button icon-name="edit" variant="outlined" size="small" :href="route('vendor.edit', $vendor->idvendor)">Edit</x-ui.button>

                    @if ($vendor->status === 'A')
                        <form
                            class="d-inline"
                            method="POST"
                            action="{{ route('vendor.destroy', $vendor->idvendor) }}"
                            data-md3-confirm="Nonaktifkan vendor “{{ $vendor->nama_vendor }}”? Vendor tetap tercatat sebagai tidak aktif."
                            data-md3-confirm-action="Nonaktifkan"
                            onsubmit="return confirm('Nonaktifkan vendor ini?')"
                        >
                            @csrf
                            @method('DELETE')
                            <x-ui.button icon-name="archive" variant="outlined" size="small" class="md3-action--error" type="submit">Nonaktifkan</x-ui.button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada data vendor.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

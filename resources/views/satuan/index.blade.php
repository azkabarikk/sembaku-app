@extends('layouts.app')

@section('title', 'Satuan')
@section('heading', 'Daftar Satuan')
@section('description', 'Data satuan barang.')

@section('page-actions')
    <x-ui.button variant="filled" :href="route('satuan.create')">
        <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
        Tambah Satuan
    </x-ui.button>
@endsection

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    <x-ui.data-table label="Daftar Satuan" :filters="['Status' => 2]">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nama Satuan</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarSatuan as $satuan)
            <tr>
                <td>{{ $satuan->idsatuan }}</td>
                <td>{{ $satuan->nama_satuan }}</td>
                <td>
                    @if ($satuan->status == 1)
                        <x-ui.chip variant="success">Aktif</x-ui.chip>
                    @else
                        <x-ui.chip variant="neutral">Tidak Aktif</x-ui.chip>
                    @endif
                </td>
                <td class="text-nowrap">
                    <x-ui.button icon-name="edit" variant="outlined" size="small" :href="route('satuan.edit', $satuan->idsatuan)">Edit</x-ui.button>

                    @if ($satuan->status == 1)
                        <form
                            class="d-inline"
                            method="POST"
                            action="{{ route('satuan.destroy', $satuan->idsatuan) }}"
                            data-md3-confirm="Nonaktifkan satuan “{{ $satuan->nama_satuan }}”? Satuan tetap tercatat sebagai tidak aktif."
                            data-md3-confirm-action="Nonaktifkan"
                            onsubmit="return confirm('Nonaktifkan satuan ini?')"
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
                <td colspan="4" class="text-center">Belum ada data satuan.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

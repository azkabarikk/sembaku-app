@extends('layouts.app')

@section('title', 'Margin Penjualan')
@section('heading', 'Margin Penjualan')
@section('description', 'Data persentase margin penjualan.')

@section('page-actions')
    <x-ui.button variant="filled" :href="route('margin-penjualan.create')">
        <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
        Tambah Margin
    </x-ui.button>
@endsection

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    <p class="md3-type-label-large md3-muted mb-0">Jumlah data: <strong>{{ count($daftarMargin) }}</strong></p>

    <x-ui.table label="Margin Penjualan">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Dibuat</th>
                <th scope="col">Persentase</th>
                <th scope="col">Status</th>
                <th scope="col">User</th>
                <th scope="col">Diperbarui</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarMargin as $margin)
            <tr>
                <td>{{ $margin->idmargin_penjualan }}</td>
                <td>{{ $margin->created_at }}</td>
                <td>{{ number_format((float) $margin->persen, 2, ',', '.') }}%</td>
                <td>
                    @if ($margin->status == 1)
                        <x-ui.chip variant="success">Aktif</x-ui.chip>
                    @else
                        <x-ui.chip variant="neutral">Tidak Aktif</x-ui.chip>
                    @endif
                </td>
                <td>{{ $margin->username }}</td>
                <td>{{ $margin->updated_at ?? '—' }}</td>
                <td class="text-nowrap">
                    <x-ui.button variant="outlined" size="small" :href="route('margin-penjualan.edit', $margin->idmargin_penjualan)">Edit</x-ui.button>

                    @if ($margin->status == 1)
                        <form
                            class="d-inline"
                            method="POST"
                            action="{{ route('margin-penjualan.destroy', $margin->idmargin_penjualan) }}"
                            onsubmit="return confirm('Nonaktifkan margin ini?')"
                        >
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="outlined" size="small" class="md3-action--error" type="submit">Nonaktifkan</x-ui.button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data margin penjualan.</td>
            </tr>
        @endforelse
    </x-ui.table>
@endsection

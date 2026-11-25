@extends('layouts.app')

@section('title', 'Pengadaan')
@section('heading', 'Daftar Pengadaan')
@section('description', 'Data pengadaan dari vendor.')

@section('page-actions')
    @can('pengadaan.kelola')
        <x-ui.button variant="filled" :href="route('pengadaan.create')">
            <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
            Tambah Pengadaan
        </x-ui.button>
    @endcan
@endsection

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif
    @if (session('error'))
        <x-ui.snackbar variant="error">{{ session('error') }}</x-ui.snackbar>
    @endif

    <x-ui.data-table label="Daftar Pengadaan" :filters="['Vendor' => 2, 'Tanggal' => 1, 'Status penerimaan' => 7]">
        <x-slot:head>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Tanggal</th>
                <th scope="col">Vendor</th>
                <th scope="col">User</th>
                <th scope="col">Subtotal</th>
                <th scope="col">PPN</th>
                <th scope="col">Total</th>
                <th scope="col">Penerimaan</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarPengadaan as $pengadaan)
            @php
                $jumlah = $ringkasan[$pengadaan->idpengadaan] ?? ['dipesan' => 0, 'sisa' => 0];
            @endphp
            <tr id="pengadaan-{{ $pengadaan->idpengadaan }}">
                <td>{{ $pengadaan->idpengadaan }}</td>
                <td>{{ $pengadaan->dibuat }}</td>
                <td>{{ $pengadaan->nama_vendor ?? '—' }}</td>
                <td>{{ $pengadaan->username ?? '—' }}</td>
                <td>Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}</td>
                <td>
                    <span data-filter-label>
                        @if ($jumlah['dipesan'] === 0)
                            Belum ada rincian
                        @elseif ($jumlah['sisa'] === 0)
                            Lengkap
                        @elseif ($pengadaan->status !== 'A')
                            Tidak bisa diterima
                        @elseif ($jumlah['sisa'] < $jumlah['dipesan'])
                            Sebagian
                        @else
                            Belum diterima
                        @endif
                    </span>
                    @if ($jumlah['sisa'] > 0)
                        <span class="d-block md3-muted">Sisa {{ $jumlah['sisa'] }} dari {{ $jumlah['dipesan'] }}</span>
                    @endif
                    @if ($pengadaan->punya_penerimaan)
                        <a class="d-block" href="{{ route('penerimaan.index', ['pengadaan' => $pengadaan->idpengadaan]) }}">Riwayat</a>
                    @endif
                </td>
                <td class="text-nowrap">
                    <x-ui.button variant="outlined" size="small" :href="route('pengadaan.show', $pengadaan->idpengadaan)">Lihat Detail</x-ui.button>
                    @if (! $pengadaan->punya_penerimaan && Gate::allows('pengadaan.kelola'))
                        <x-ui.button variant="outlined" size="small" :href="route('pengadaan.edit', $pengadaan->idpengadaan)">Edit</x-ui.button>
                        <form
                            class="d-inline"
                            method="POST"
                            action="{{ route('pengadaan.destroy', $pengadaan->idpengadaan) }}"
                            onsubmit="return confirm('Hapus permanen pengadaan ini?')"
                        >
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="outlined" size="small" class="md3-action--error" type="submit">Hapus</x-ui.button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data pengadaan.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

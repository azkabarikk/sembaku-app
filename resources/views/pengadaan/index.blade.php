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

    <x-ui.data-table compact label="Daftar Pengadaan"
        :filters="['Sisa barang' => 0, 'Vendor' => 2, 'Tanggal' => 1, 'Status penerimaan' => 7]">
        <x-slot:head>
            <tr>
                <th scope="col" class="md3-table__identity">ID</th>
                <th scope="col">Tanggal</th>
                <th scope="col">Vendor</th>
                <th scope="col">User</th>
                <th scope="col">Subtotal</th>
                <th scope="col">PPN</th>
                <th scope="col">Total</th>
                <th scope="col">Penerimaan</th>
                <th scope="col" class="md3-table__action-column">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarPengadaan as $pengadaan)
            @php
                $jumlah = $ringkasan[$pengadaan->idpengadaan] ?? ['dipesan' => 0, 'sisa' => 0];
                $statusPenerimaan = match (true) {
                    $jumlah['dipesan'] === 0 => 'Belum ada rincian',
                    $jumlah['sisa'] === 0 => 'Lengkap',
                    $pengadaan->status !== 'A' => 'Tidak bisa diterima',
                    $jumlah['sisa'] < $jumlah['dipesan'] => 'Sebagian',
                    default => 'Belum diterima',
                };
                $varianStatus = match ($statusPenerimaan) {
                    'Lengkap' => 'success',
                    'Tidak bisa diterima' => 'error',
                    'Sebagian', 'Belum diterima' => 'warning',
                    default => 'neutral',
                };
            @endphp
            <tr id="pengadaan-{{ $pengadaan->idpengadaan }}">
                <td class="md3-table__identity" data-filter-value="{{ $jumlah['sisa'] > 0 ? 'Masih ada sisa' : 'Tidak ada sisa' }}">
                    <strong>#{{ $pengadaan->idpengadaan }}</strong>
                    @if ($jumlah['sisa'] > 0)
                        <div class="mt-1">
                            <x-ui.chip variant="warning" class="md3-pengadaan-sisa">Sisa {{ number_format($jumlah['sisa'], 0, ',', '.') }}</x-ui.chip>
                        </div>
                    @endif
                </td>
                <td>{{ $pengadaan->dibuat }}</td>
                <td>{{ $pengadaan->nama_vendor ?? '—' }}</td>
                <td>{{ $pengadaan->username ?? '—' }}</td>
                <td>Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}</td>
                <td>
                    <x-ui.chip :variant="$varianStatus" data-filter-label>{{ $statusPenerimaan }}</x-ui.chip>
                    @if ($jumlah['dipesan'] > 0)
                        <span class="d-block md3-muted mt-1">
                            {{ number_format($jumlah['dipesan'] - $jumlah['sisa'], 0, ',', '.') }} dari
                            {{ number_format($jumlah['dipesan'], 0, ',', '.') }} diterima
                        </span>
                    @endif
                    @if ($pengadaan->punya_penerimaan)
                        <a class="d-block" href="{{ route('penerimaan.index', ['pengadaan' => $pengadaan->idpengadaan]) }}">Riwayat</a>
                    @endif
                </td>
                <td class="md3-table__action-column">
                    <div class="md3-cluster md3-table__actions">
                        <x-ui.button icon-name="eye" variant="outlined" size="small" :href="route('pengadaan.show', $pengadaan->idpengadaan)"><span class="md3-table__action-label">Lihat Detail</span></x-ui.button>
                        @if (! $pengadaan->punya_penerimaan && Gate::allows('pengadaan.kelola'))
                            <x-ui.button icon-name="edit" variant="outlined" size="small" :href="route('pengadaan.edit', $pengadaan->idpengadaan)"><span class="md3-table__action-label">Edit</span></x-ui.button>
                            <form
                                class="d-inline"
                                method="POST"
                                action="{{ route('pengadaan.destroy', $pengadaan->idpengadaan) }}"
                                data-md3-confirm="Hapus pengadaan #{{ $pengadaan->idpengadaan }} beserta rincian barangnya?"
                                data-md3-confirm-action="Hapus"
                                onsubmit="return confirm('Hapus permanen pengadaan ini?')"
                            >
                                @csrf
                                @method('DELETE')
                                <x-ui.button icon-name="trash" variant="outlined" size="small" class="md3-action--error" type="submit"><span class="md3-table__action-label">Hapus</span></x-ui.button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data pengadaan.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

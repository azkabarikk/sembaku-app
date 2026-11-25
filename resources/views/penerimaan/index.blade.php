@extends('layouts.app')

@section('title', 'Penerimaan')
@section('heading', 'Daftar Penerimaan')
@section('description', 'Barang yang diterima berdasarkan pengadaan.')

@section('page-actions')
    @can('penerimaan.draf')
        <x-ui.button variant="filled" type="button" data-bs-toggle="collapse"
            data-bs-target="#formTambahPenerimaan" aria-controls="formTambahPenerimaan"
            aria-expanded="false" :disabled="$pengadaanTersisa === []">
            <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
            Tambah Penerimaan
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

    @error('penerimaan')
        <x-ui.alert variant="error">{{ $message }}</x-ui.alert>
    @enderror

    @if ($idPengadaan)
        <div class="d-flex align-items-center gap-3 mb-3">
            <a href="{{ route('pengadaan.show', $idPengadaan) }}">Pengadaan #{{ $idPengadaan }}</a>
            <a href="{{ route('penerimaan.index') }}">Semua penerimaan</a>
        </div>
    @endif

    @can('penerimaan.draf')
        <div class="collapse" id="formTambahPenerimaan">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ route('penerimaan.pilih-pengadaan') }}">
                <div class="col-sm-8 col-lg-6">
                    <label class="md3-field__label" for="pilihPengadaan">Pengadaan yang bisa diterima</label>
                    <select class="md3-control md3-control--select" id="pilihPengadaan" name="pengadaan" required @disabled($pengadaanTersisa === [])>
                        <option value="">Pilih pengadaan</option>
                        @foreach ($pengadaanTersisa as $pesanan)
                            <option value="{{ $pesanan->idpengadaan }}" @selected($idPengadaan === (int) $pesanan->idpengadaan)>
                                #{{ $pesanan->idpengadaan }} · {{ $pesanan->nama_vendor ?? '—' }} · Sisa {{ $pesanan->jumlah_sisa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <x-ui.button variant="filled" type="submit" :disabled="$pengadaanTersisa === []">Terima Barang</x-ui.button>
                </div>
            </form>
        </div>
        @if ($pengadaanTersisa === [])
            <p class="md3-muted mb-4">Tidak ada pengadaan yang masih menunggu barang.</p>
        @endif
    @endcan

    <x-ui.data-table label="Daftar Penerimaan" :filters="['Tanggal' => 1, 'Status' => 5]">
        <x-slot:head>
            <tr>
                <th scope="col">ID Penerimaan</th>
                <th scope="col">Tanggal</th>
                <th scope="col">ID Pengadaan</th>
                <th scope="col">Vendor</th>
                <th scope="col">User</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarPenerimaan as $penerimaan)
            <tr id="penerimaan-{{ $penerimaan->idpenerimaan }}">
                <td>{{ $penerimaan->idpenerimaan }}</td>
                <td>{{ $penerimaan->created_at }}</td>
                <td><a href="{{ route('pengadaan.show', $penerimaan->idpengadaan) }}">#{{ $penerimaan->idpengadaan }}</a></td>
                <td>{{ $penerimaan->nama_vendor ?? '—' }}</td>
                <td>{{ $penerimaan->username ?? '—' }}</td>
                <td>
                    <x-ui.chip :variant="in_array($penerimaan->status, ['A', 'K'], true) ? 'success' : 'neutral'">
                        {{ match ($penerimaan->status) {
                        'D' => 'Draf',
                        'K' => 'Terkonfirmasi',
                        'A' => 'Aktif (lama)',
                        'N' => 'Dibatalkan (lama)',
                        default => $penerimaan->status,
                        } }}
                    </x-ui.chip>
                </td>
                <td class="text-nowrap">
                    <x-ui.button variant="outlined" size="small" :href="route('penerimaan.show', $penerimaan->idpenerimaan)">Lihat Detail</x-ui.button>
                    @if ($penerimaan->status !== 'D')
                        <x-ui.button variant="outlined" size="small" :href="route('kartu-stok.index', ['penerimaan' => $penerimaan->idpenerimaan])">Kartu Stok</x-ui.button>
                    @endif
                    @can('penerimaan.ubah-draf', $penerimaan)
                        <x-ui.button variant="outlined" size="small" :href="route('penerimaan.edit', $penerimaan->idpenerimaan)">Edit</x-ui.button>
                        <form
                            class="d-inline"
                            method="POST"
                            action="{{ route('penerimaan.destroy', $penerimaan->idpenerimaan) }}"
                            onsubmit="return confirm('Hapus draf penerimaan ini?')"
                        >
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="outlined" size="small" class="md3-action--error" type="submit">Hapus</x-ui.button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data penerimaan.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

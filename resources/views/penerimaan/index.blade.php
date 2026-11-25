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
            <form class="md3-form" method="GET" action="{{ route('penerimaan.pilih-pengadaan') }}">
                <x-ui.form-section title="Pilih pengadaan" icon="clipboard-list"
                    description="Pilihan berisi pengadaan yang masih memiliki sisa barang.">
                    <x-ui.select label="Pengadaan yang bisa diterima" id="pilihPengadaan" name="pengadaan"
                        :disabled="$pengadaanTersisa === []" required>
                        <option value="">Pilih pengadaan</option>
                        @foreach ($pengadaanTersisa as $pesanan)
                            <option value="{{ $pesanan->idpengadaan }}" @selected($idPengadaan === (int) $pesanan->idpengadaan)>
                                #{{ $pesanan->idpengadaan }} · {{ $pesanan->nama_vendor ?? '—' }} · Sisa {{ $pesanan->jumlah_sisa }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    <div class="md3-form-actions">
                        <x-ui.button icon-name="package-check" type="submit" :disabled="$pengadaanTersisa === []">Terima Barang</x-ui.button>
                    </div>
                </x-ui.form-section>
            </form>
        </div>
        @if ($pengadaanTersisa === [])
            <p class="md3-muted mb-4">Tidak ada pengadaan yang masih menunggu barang.</p>
        @endif
    @endcan

    <x-ui.data-table compact label="Daftar Penerimaan" :filters="['Tanggal' => 1, 'Status' => 5]">
        <x-slot:head>
            <tr>
                <th scope="col" class="md3-table__identity">ID Penerimaan</th>
                <th scope="col">Tanggal</th>
                <th scope="col">ID Pengadaan</th>
                <th scope="col">Vendor</th>
                <th scope="col">User</th>
                <th scope="col">Status</th>
                <th scope="col" class="md3-table__action-column">Aksi</th>
            </tr>
        </x-slot:head>
        @forelse ($daftarPenerimaan as $penerimaan)
            <tr id="penerimaan-{{ $penerimaan->idpenerimaan }}">
                <td class="md3-table__identity">{{ $penerimaan->idpenerimaan }}</td>
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
                <td class="md3-table__action-column">
                    <div class="md3-cluster md3-table__actions">
                        <x-ui.button icon-name="eye" variant="outlined" size="small" :href="route('penerimaan.show', $penerimaan->idpenerimaan)"><span class="md3-table__action-label">Lihat Detail</span></x-ui.button>
                        @can('kartu-stok.baca')
                            @if ($penerimaan->status !== 'D')
                                <x-ui.button icon-name="clipboard-list" variant="outlined" size="small" :href="route('kartu-stok.index', ['penerimaan' => $penerimaan->idpenerimaan])">
                                    <span class="md3-table__action-label">Lihat Mutasi Stok</span>
                                </x-ui.button>
                            @endif
                        @endcan
                        @can('penerimaan.ubah-draf', $penerimaan)
                            <x-ui.button icon-name="edit" variant="outlined" size="small" :href="route('penerimaan.edit', $penerimaan->idpenerimaan)"><span class="md3-table__action-label">Edit</span></x-ui.button>
                            <form
                                class="d-inline"
                                method="POST"
                                action="{{ route('penerimaan.destroy', $penerimaan->idpenerimaan) }}"
                                data-md3-confirm="Hapus draf penerimaan #{{ $penerimaan->idpenerimaan }} beserta rinciannya?"
                                data-md3-confirm-action="Hapus"
                                onsubmit="return confirm('Hapus draf penerimaan ini?')"
                            >
                                @csrf
                                @method('DELETE')
                                <x-ui.button icon-name="trash" variant="outlined" size="small" class="md3-action--error" type="submit"><span class="md3-table__action-label">Hapus</span></x-ui.button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data penerimaan.</td>
            </tr>
        @endforelse
    </x-ui.data-table>
@endsection

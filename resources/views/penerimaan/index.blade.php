@extends('layouts.app')

@section('title', 'Penerimaan')
@section('heading', 'Daftar Penerimaan')
@section('description', 'Barang yang diterima berdasarkan pengadaan.')

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

    @error('penerimaan')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    @if ($idPengadaan)
        <div class="d-flex align-items-center gap-3 mb-3">
            <a href="{{ route('pengadaan.show', $idPengadaan) }}">Pengadaan #{{ $idPengadaan }}</a>
            <a href="{{ route('penerimaan.index') }}">Semua penerimaan</a>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarPenerimaan) }}</strong></p>
        @can('penerimaan.draf')
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                data-bs-target="#formTambahPenerimaan" aria-controls="formTambahPenerimaan"
                aria-expanded="false" @disabled($pengadaanTersisa === [])>
                Tambah Penerimaan
            </button>
        @endcan
    </div>

    @can('penerimaan.draf')
        <div class="collapse" id="formTambahPenerimaan">
            <form class="row g-2 align-items-end mb-3" method="GET" action="{{ route('penerimaan.pilih-pengadaan') }}">
                <div class="col-sm-8 col-lg-6">
                    <label class="form-label" for="pilihPengadaan">Pengadaan yang bisa diterima</label>
                    <select class="form-select" id="pilihPengadaan" name="pengadaan" required @disabled($pengadaanTersisa === [])>
                        <option value="">Pilih pengadaan</option>
                        @foreach ($pengadaanTersisa as $pesanan)
                            <option value="{{ $pesanan->idpengadaan }}" @selected($idPengadaan === (int) $pesanan->idpengadaan)>
                                #{{ $pesanan->idpengadaan }} · {{ $pesanan->nama_vendor ?? '—' }} · Sisa {{ $pesanan->jumlah_sisa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit" @disabled($pengadaanTersisa === [])>Terima Barang</button>
                </div>
            </form>
        </div>
        @if ($pengadaanTersisa === [])
            <p class="text-muted mb-4">Tidak ada pengadaan yang masih menunggu barang.</p>
        @endif
    @endcan

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID Penerimaan</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">ID Pengadaan</th>
                    <th scope="col">Vendor</th>
                    <th scope="col">User</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarPenerimaan as $penerimaan)
                    <tr id="penerimaan-{{ $penerimaan->idpenerimaan }}">
                        <td>{{ $penerimaan->idpenerimaan }}</td>
                        <td>{{ $penerimaan->created_at }}</td>
                        <td><a href="{{ route('pengadaan.show', $penerimaan->idpengadaan) }}">#{{ $penerimaan->idpengadaan }}</a></td>
                        <td>{{ $penerimaan->nama_vendor ?? '—' }}</td>
                        <td>{{ $penerimaan->username ?? '—' }}</td>
                        <td>
                            <span class="badge {{ in_array($penerimaan->status, ['A', 'K'], true) ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ match ($penerimaan->status) {
                                'D' => 'Draf',
                                'K' => 'Terkonfirmasi',
                                'A' => 'Aktif (lama)',
                                'N' => 'Dibatalkan (lama)',
                                default => $penerimaan->status,
                                } }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('penerimaan.show', $penerimaan->idpenerimaan) }}">Lihat Detail</a>
                            @if ($penerimaan->status !== 'D')
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('kartu-stok.index', ['penerimaan' => $penerimaan->idpenerimaan]) }}">Kartu Stok</a>
                            @endif
                            @can('penerimaan.ubah-draf', $penerimaan)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('penerimaan.edit', $penerimaan->idpenerimaan) }}">Edit</a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('penerimaan.destroy', $penerimaan->idpenerimaan) }}"
                                    onsubmit="return confirm('Hapus draf penerimaan ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data penerimaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

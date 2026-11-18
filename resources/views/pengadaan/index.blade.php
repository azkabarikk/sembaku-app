@extends('layouts.app')

@section('title', 'Pengadaan')
@section('heading', 'Daftar Pengadaan')
@section('description', 'Data pengadaan dari vendor.')

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

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarPengadaan) }}</strong></p>
        @can('pengadaan.kelola')
            <a class="btn btn-primary" href="{{ route('pengadaan.create') }}">Tambah Pengadaan</a>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
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
            </thead>
            <tbody>
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
                            @if ($jumlah['sisa'] > 0)
                                <span class="d-block text-muted">Sisa {{ $jumlah['sisa'] }} dari {{ $jumlah['dipesan'] }}</span>
                            @endif
                            @if ($pengadaan->punya_penerimaan)
                                <a class="d-block" href="{{ route('penerimaan.index', ['pengadaan' => $pengadaan->idpengadaan]) }}">Riwayat</a>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('pengadaan.show', $pengadaan->idpengadaan) }}">Lihat Detail</a>
                            @if (! $pengadaan->punya_penerimaan && Gate::allows('pengadaan.kelola'))
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('pengadaan.edit', $pengadaan->idpengadaan) }}">Edit</a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('pengadaan.destroy', $pengadaan->idpengadaan) }}"
                                    onsubmit="return confirm('Hapus permanen pengadaan ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data pengadaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

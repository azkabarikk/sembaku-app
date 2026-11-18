@extends('layouts.app')

@section('title', 'Detail Pengadaan')
@section('heading', 'Detail Pengadaan #' . $pengadaan->idpengadaan)

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <dl class="row mb-3">
        <dt class="col-sm-3">Tanggal</dt>
        <dd class="col-sm-9">{{ $pengadaan->dibuat }}</dd>
        <dt class="col-sm-3">Vendor</dt>
        <dd class="col-sm-9">{{ $pengadaan->nama_vendor ?? '—' }}</dd>
        <dt class="col-sm-3">User pencatat</dt>
        <dd class="col-sm-9">{{ $pengadaan->username ?? '—' }}</dd>
        <dt class="col-sm-3">Status penerimaan</dt>
        <dd class="col-sm-9">{{ match ($pengadaan->status) { 'A' => 'Siap diterima', 'N' => 'Ditahan', default => $pengadaan->status } }}</dd>
    </dl>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">Barang</th>
                    <th scope="col">Satuan</th>
                    <th scope="col">Dipesan</th>
                    <th scope="col">Diterima</th>
                    <th scope="col">Sisa</th>
                    <th scope="col">Harga Satuan</th>
                    <th scope="col">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rincian as $detail)
                    <tr>
                        <td>{{ $detail->nama_barang ?? '—' }}</td>
                        <td>{{ $detail->nama_satuan ?? '—' }}</td>
                        <td>{{ $detail->jumlah }}</td>
                        <td>{{ $detail->jumlah_diterima }}</td>
                        <td>{{ $detail->jumlah_sisa }}</td>
                        <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Belum ada rincian barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <dl class="row">
        <dt class="col-sm-3">Subtotal</dt>
        <dd class="col-sm-9">Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">PPN</dt>
        <dd class="col-sm-9">Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Total</dt>
        <dd class="col-sm-9">Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}</dd>
    </dl>

    <a class="btn btn-secondary" href="{{ route('pengadaan.index') }}">Kembali</a>
    <a class="btn btn-outline-secondary" href="{{ route('penerimaan.index', ['pengadaan' => $pengadaan->idpengadaan]) }}">Riwayat Penerimaan</a>
@endsection

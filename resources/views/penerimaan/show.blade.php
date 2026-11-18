@extends('layouts.app')

@section('title', 'Detail Penerimaan')
@section('heading', 'Detail Penerimaan #' . $penerimaan->idpenerimaan)

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <dl class="row mb-3">
        <dt class="col-sm-3">Pengadaan</dt>
        <dd class="col-sm-9"><a href="{{ route('pengadaan.show', $penerimaan->idpengadaan) }}">#{{ $penerimaan->idpengadaan }}</a></dd>
        <dt class="col-sm-3">Vendor</dt>
        <dd class="col-sm-9">{{ $penerimaan->nama_vendor ?? '—' }}</dd>
        <dt class="col-sm-3">Tanggal</dt>
        <dd class="col-sm-9">{{ $penerimaan->created_at }}</dd>
        <dt class="col-sm-3">User pencatat</dt>
        <dd class="col-sm-9">{{ $penerimaan->username ?? '—' }}</dd>
        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">{{ match ($penerimaan->status) {
            'D' => 'Draf',
            'K' => 'Terkonfirmasi',
            'A' => 'Aktif (lama)',
            'N' => 'Dibatalkan (lama)',
            default => $penerimaan->status,
        } }}</dd>
    </dl>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">Barang</th>
                    <th scope="col">Satuan</th>
                    <th scope="col">Dipesan</th>
                    <th scope="col">Diterima</th>
                    <th scope="col">Harga Satuan Terima</th>
                    <th scope="col">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rincian as $detail)
                    <tr>
                        <td>{{ $detail->nama_barang ?? '—' }}</td>
                        <td>{{ $detail->nama_satuan ?? '—' }}</td>
                        <td>{{ $detail->jumlah_dipesan }}</td>
                        <td>{{ $detail->jumlah_terima }}</td>
                        <td>Rp {{ number_format($detail->harga_satuan_terima, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->sub_total_terima, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Belum ada rincian barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-secondary" href="{{ route('penerimaan.index') }}">Kembali</a>
        @can('penerimaan.ubah-draf', $penerimaan)
            <a class="btn btn-outline-primary" href="{{ route('penerimaan.edit', $penerimaan->idpenerimaan) }}">Edit</a>
        @endcan
        @if ($penerimaan->status === 'D')
            @can('penerimaan.konfirmasi')
                <form method="POST" action="{{ route('penerimaan.confirm', $penerimaan->idpenerimaan) }}" onsubmit="return confirm('Konfirmasi penerimaan dan masukkan barang ke stok?')">
                    @csrf
                    <button class="btn btn-primary" type="submit">Konfirmasi</button>
                </form>
            @endcan
        @else
            <a class="btn btn-outline-secondary" href="{{ route('kartu-stok.index', ['penerimaan' => $penerimaan->idpenerimaan]) }}">Kartu Stok</a>
        @endif
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Pengadaan')
@section('heading', 'Detail Pengadaan #' . $pengadaan->idpengadaan)

@section('content')
    @if (session('success'))
        <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
    @endif

    <dl class="md3-record">
        <dt>Tanggal</dt>
        <dd>{{ $pengadaan->dibuat }}</dd>
        <dt>Vendor</dt>
        <dd>{{ $pengadaan->nama_vendor ?? '—' }}</dd>
        <dt>User pencatat</dt>
        <dd>{{ $pengadaan->username ?? '—' }}</dd>
        <dt>Status penerimaan</dt>
        <dd>{{ match ($pengadaan->status) { 'A' => 'Siap diterima', 'N' => 'Ditahan', default => $pengadaan->status } }}</dd>
    </dl>

    <x-ui.data-table label="Detail Pengadaan">
        <x-slot:head>
            <tr>
                <th scope="col">Barang</th>
                <th scope="col">Satuan</th>
                <th scope="col">Dipesan</th>
                <th scope="col">Diterima</th>
                <th scope="col">Sisa</th>
                <th scope="col">Harga Satuan</th>
                <th scope="col">Subtotal</th>
            </tr>
        </x-slot:head>
        @forelse ($rincian as $detail)
            <tr>
                <td>{{ $detail->nama_barang ?? '—' }}</td>
                <td>{{ $detail->nama_satuan ?? '—' }}</td>
                <td>{{ $detail->jumlah }}</td>
                <td>{{ $detail->jumlah_diterima }}</td>
                <td class="md3-table__numeric">
                    @if ($detail->jumlah_sisa > 0)
                        <x-ui.chip variant="warning">{{ number_format($detail->jumlah_sisa, 0, ',', '.') }}</x-ui.chip>
                    @else
                        <span class="md3-muted">0</span>
                    @endif
                </td>
                <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($detail->sub_total, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Belum ada rincian barang.</td></tr>
        @endforelse
    </x-ui.data-table>

    <dl class="md3-record">
        <dt>Subtotal</dt>
        <dd>Rp {{ number_format($pengadaan->subtotal_nilai, 0, ',', '.') }}</dd>
        <dt>PPN</dt>
        <dd>Rp {{ number_format($pengadaan->ppn, 0, ',', '.') }}</dd>
        <dt>Total</dt>
        <dd>Rp {{ number_format($pengadaan->total_nilai, 0, ',', '.') }}</dd>
    </dl>

    <div class="md3-cluster">
        <x-ui.button variant="tonal" :href="route('pengadaan.index')">Kembali</x-ui.button>
        @can('penerimaan.baca')
            <x-ui.button variant="outlined" icon-name="package-check" :href="route('penerimaan.index', ['pengadaan' => $pengadaan->idpengadaan])">
                Lihat Riwayat Penerimaan
            </x-ui.button>
        @endcan
    </div>
@endsection

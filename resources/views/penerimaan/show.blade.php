@extends('layouts.app')

@section('title', 'Detail Penerimaan')
@section('heading', 'Detail Penerimaan #' . $penerimaan->idpenerimaan)

@section('content')
    @if (session('success'))
        <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert variant="error">{{ session('error') }}</x-ui.alert>
    @endif

    <dl class="md3-record">
        <dt>Pengadaan</dt>
        <dd>
            @can('pengadaan.baca')
                <a href="{{ route('pengadaan.show', $penerimaan->idpengadaan) }}">Lihat Pengadaan #{{ $penerimaan->idpengadaan }}</a>
            @else
                #{{ $penerimaan->idpengadaan }}
            @endcan
        </dd>
        <dt>Vendor</dt>
        <dd>{{ $penerimaan->nama_vendor ?? '—' }}</dd>
        <dt>Tanggal</dt>
        <dd>{{ $penerimaan->created_at }}</dd>
        <dt>User pencatat</dt>
        <dd>{{ $penerimaan->username ?? '—' }}</dd>
        <dt>Status</dt>
        <dd>{{ match ($penerimaan->status) {
            'D' => 'Draf',
            'K' => 'Terkonfirmasi',
            'A' => 'Aktif (lama)',
            'N' => 'Dibatalkan (lama)',
            default => $penerimaan->status,
        } }}</dd>
    </dl>

    <x-ui.data-table label="Detail Penerimaan">
        <x-slot:head>
            <tr>
                <th scope="col">Barang</th>
                <th scope="col">Satuan</th>
                <th scope="col">Dipesan</th>
                <th scope="col">Diterima</th>
                <th scope="col">Harga Satuan Terima</th>
                <th scope="col">Subtotal</th>
            </tr>
        </x-slot:head>
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
    </x-ui.data-table>

    <section class="md3-stack" aria-labelledby="nilai-pengadaan-asal">
        <h2 class="md3-type-title-large mb-0" id="nilai-pengadaan-asal">Nilai Pengadaan Asal</h2>
        <dl class="md3-record">
            <dt>Subtotal Pengadaan</dt>
            <dd>Rp {{ number_format($penerimaan->subtotal_pengadaan, 0, ',', '.') }}</dd>
            <dt>PPN (11%)</dt>
            <dd>Rp {{ number_format($penerimaan->ppn_pengadaan, 0, ',', '.') }}</dd>
            <dt>Total Pengadaan</dt>
            <dd>Rp {{ number_format($penerimaan->total_pengadaan, 0, ',', '.') }}</dd>
        </dl>
    </section>

    <div class="md3-cluster">
        <x-ui.button variant="tonal" :href="route('penerimaan.index')">Kembali</x-ui.button>
        @can('penerimaan.ubah-draf', $penerimaan)
            <x-ui.button icon-name="edit" variant="outlined" :href="route('penerimaan.edit', $penerimaan->idpenerimaan)">Edit</x-ui.button>
        @endcan
        @can('kartu-stok.baca')
            @if ($penerimaan->status !== 'D')
                <x-ui.button variant="outlined" icon-name="clipboard-list" :href="route('kartu-stok.index', ['penerimaan' => $penerimaan->idpenerimaan])">
                    Lihat Mutasi Stok
                </x-ui.button>
            @endif
        @endcan
    </div>
@endsection

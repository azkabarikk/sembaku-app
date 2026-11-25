@extends('layouts.app')

@section('title', 'Stok Barang')
@section('heading', 'Stok Barang')

@section('content')
    @php
        $kondisi = [
            1 => ['label' => 'Tersedia', 'variant' => 'success', 'jumlah' => 0],
            0 => ['label' => 'Habis', 'variant' => 'warning', 'jumlah' => 0],
        ];
        foreach ($stok as $barang) {
            $kondisi[(int) ($barang->saldo > 0)]['jumlah']++;
        }
    @endphp

    <section class="md3-grid" aria-label="Ringkasan kondisi semua barang">
        @foreach ($kondisi as $status)
            <x-ui.card variant="outlined">
                <div><x-ui.chip :variant="$status['variant']">{{ $status['label'] }}</x-ui.chip></div>
                <div><strong class="md3-type-headline-small">{{ $status['jumlah'] }}</strong> <span class="md3-muted">barang</span></div>
            </x-ui.card>
        @endforeach
    </section>

    <x-ui.data-table compact label="Stok Barang" :filters="['Kondisi stok' => 6]">
        <x-slot:head>
            <tr>
                <th scope="col" class="md3-table__identity">Barang</th>
                <th scope="col">ID</th>
                <th scope="col">Satuan</th>
                <th scope="col" class="md3-table__numeric">Total Masuk</th>
                <th scope="col" class="md3-table__numeric">Total Keluar</th>
                <th scope="col" class="md3-table__numeric">Saldo Akhir</th>
                <th scope="col">Kondisi</th>
            </tr>
        </x-slot:head>
        @forelse ($stok as $barang)
            @php($status = $kondisi[(int) ($barang->saldo > 0)])
            <tr>
                <td class="md3-table__identity"><strong>{{ $barang->nama }}</strong></td>
                <td class="md3-muted">{{ $barang->idbarang }}</td>
                <td>{{ $barang->nama_satuan }}</td>
                <td class="md3-table__numeric">{{ $barang->total_masuk }}</td>
                <td class="md3-table__numeric">{{ $barang->total_keluar }}</td>
                <td class="md3-table__numeric">
                    <strong>{{ $barang->saldo }}</strong>
                    @if ((int) $barang->saldo !== (int) $barang->saldo_kartu)
                        <span class="d-block md3-field__error">Saldo kartu: {{ $barang->saldo_kartu }}</span>
                    @endif
                </td>
                <td><x-ui.chip :variant="$status['variant']">{{ $status['label'] }}</x-ui.chip></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Belum ada barang.</td></tr>
        @endforelse
    </x-ui.data-table>
@endsection

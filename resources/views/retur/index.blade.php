@extends('layouts.app')

@section('title', 'Retur')
@section('heading', 'Retur ke Vendor')
@section('description', 'Riwayat barang yang dikembalikan kepada vendor.')

@section('content')
    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    @can('retur.buat')
        <x-ui.card variant="outlined">
            <x-slot:header>Mulai retur dari penerimaan</x-slot:header>
            <x-ui.data-table compact label="Penerimaan yang dapat diretur" :numbered="true">
                <x-slot:head>
                    <tr>
                        <th scope="col" class="md3-table__identity">Penerimaan</th>
                        <th scope="col">Pengadaan</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Vendor</th>
                        <th scope="col">Petugas</th>
                        <th scope="col" class="md3-table__action-column">Aksi</th>
                    </tr>
                </x-slot:head>
                @forelse ($daftarSumber as $sumber)
                    <tr>
                        <td class="md3-table__identity">#{{ $sumber->idpenerimaan }}</td>
                        <td>#{{ $sumber->idpengadaan }}</td>
                        <td>{{ $sumber->created_at }}</td>
                        <td>{{ $sumber->nama_vendor ?? '—' }}</td>
                        <td>{{ $sumber->username ?? '—' }}</td>
                        <td class="md3-table__action-column">
                            <x-ui.button icon-name="package-check" variant="outlined" size="small" :href="route('retur.create', $sumber->idpenerimaan)">
                                <span class="md3-table__action-label">Pilih Barang</span>
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="Belum ada penerimaan yang dapat diretur." /></td></tr>
                @endforelse
            </x-ui.data-table>
        </x-ui.card>
    @endcan

    <x-ui.card variant="outlined">
        <x-slot:header>Riwayat retur</x-slot:header>
        <x-ui.data-table compact label="Riwayat retur" :numbered="true">
            <x-slot:head>
                <tr>
                    <th scope="col" class="md3-table__identity">Retur</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Penerimaan</th>
                    <th scope="col">Pengadaan</th>
                    <th scope="col">Vendor</th>
                    <th scope="col">Petugas</th>
                    <th scope="col" class="md3-table__action-column">Aksi</th>
                </tr>
            </x-slot:head>
            @forelse ($daftarRetur as $retur)
                <tr>
                    <td class="md3-table__identity">#{{ $retur->idretur }}</td>
                    <td>{{ $retur->created_at }}</td>
                    <td>#{{ $retur->idpenerimaan }}</td>
                    <td>#{{ $retur->idpengadaan }}</td>
                    <td>{{ $retur->nama_vendor ?? '—' }}</td>
                    <td>{{ $retur->username ?? '—' }}</td>
                    <td class="md3-table__action-column"><x-ui.button icon-name="eye" variant="outlined" size="small" :href="route('retur.show', $retur->idretur)"><span class="md3-table__action-label">Lihat Detail</span></x-ui.button></td>
                </tr>
            @empty
                <tr><td colspan="7"><x-ui.empty-state title="Belum ada retur." /></td></tr>
            @endforelse
        </x-ui.data-table>
    </x-ui.card>
@endsection

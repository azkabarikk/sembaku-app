@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('heading', 'Kartu Stok')

@section('content')
    <form method="GET" action="{{ route('kartu-stok.index') }}" class="row g-2 align-items-end mb-3">
        @if ($idPenerimaan)
            <input type="hidden" name="penerimaan" value="{{ $idPenerimaan }}">
        @endif
        <div class="col-sm-4">
            <label class="md3-field__label" for="barang">Barang</label>
            <select class="md3-control md3-control--select" id="barang" name="barang">
                <option value="">Semua barang</option>
                @foreach ($daftarBarang as $barang)
                    <option value="{{ $barang->idbarang }}" @selected($idBarang == $barang->idbarang)>{{ $barang->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <label class="md3-field__label" for="jenis">Jenis transaksi</label>
            <select class="md3-control md3-control--select" id="jenis" name="jenis">
                <option value="">Semua jenis</option>
                @foreach ($daftarJenis as $kode)
                    <option value="{{ $kode }}" @selected($jenis === $kode)>{{ match ($kode) {
                        'M', 'P' => 'Penerimaan ('.$kode.')',
                        'B' => 'Koreksi Penerimaan (B)',
                        default => $kode,
                    } }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <label class="md3-field__label" for="tanggal">Tanggal</label>
            <input class="md3-control" id="tanggal" name="tanggal" type="date" value="{{ $tanggal }}">
        </div>
        <div class="col-auto">
            <x-ui.button variant="filled" type="submit">Tampilkan</x-ui.button>
            @if ($idBarang || $idPenerimaan || $jenis || $tanggal)
                <x-ui.button variant="outlined" :href="route('kartu-stok.index')">Reset</x-ui.button>
            @endif
        </div>
    </form>

    @if ($barangDipilih)
        <p>Stok saat ini: <strong>{{ $barangDipilih->stok }}</strong></p>
    @endif
    @if ($idPenerimaan && Gate::allows('penerimaan.baca'))
        <p><a href="{{ route('penerimaan.show', $idPenerimaan) }}">Penerimaan #{{ $idPenerimaan }}</a></p>
    @endif

    <x-ui.table label="Kartu Stok">
        <x-slot:head>
            <tr>
                <th scope="col">Tanggal</th>
                <th scope="col">Barang</th>
                <th scope="col">Jenis</th>
                <th scope="col">Transaksi</th>
                <th scope="col">Masuk</th>
                <th scope="col">Keluar</th>
                <th scope="col">Saldo</th>
            </tr>
        </x-slot:head>
        @forelse ($mutasi as $baris)
            <tr>
                <td>{{ $baris->created_at }}</td>
                <td>{{ $baris->nama_barang ?? '—' }}</td>
                <td>{{ match ($baris->jenis_transaksi) {
                    'M', 'P' => 'Penerimaan',
                    'B' => 'Koreksi Penerimaan',
                    default => $baris->jenis_transaksi,
                } }}</td>
                <td>
                    @if ($baris->idpenerimaan)
                        <a href="{{ route('penerimaan.show', $baris->idpenerimaan) }}">#{{ $baris->idtransaksi }}</a>
                    @else
                        #{{ $baris->idtransaksi }}
                    @endif
                </td>
                <td>{{ $baris->masuk }}</td>
                <td>{{ $baris->keluar }}</td>
                <td>{{ $baris->stock }}</td>
            </tr>
        @empty
            <tr>
                <td class="text-center" colspan="7">Belum ada mutasi stok untuk pilihan ini.</td>
            </tr>
        @endforelse
    </x-ui.table>

    @if ($mutasi->hasPages())
        <x-ui.pagination>{{ $mutasi->links('pagination::simple-bootstrap-5') }}</x-ui.pagination>
    @endif
@endsection

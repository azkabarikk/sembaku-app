@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('heading', 'Kartu Stok')

@section('content')
    <form method="GET" action="{{ route('kartu-stok.index') }}" class="row g-2 align-items-end mb-3">
        @if ($idPenerimaan)
            <input type="hidden" name="penerimaan" value="{{ $idPenerimaan }}">
        @endif
        <div class="col-sm-4">
            <label class="form-label" for="barang">Barang</label>
            <select class="form-select" id="barang" name="barang">
                <option value="">Semua barang</option>
                @foreach ($daftarBarang as $barang)
                    <option value="{{ $barang->idbarang }}" @selected($idBarang == $barang->idbarang)>{{ $barang->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <label class="form-label" for="jenis">Jenis transaksi</label>
            <select class="form-select" id="jenis" name="jenis">
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
            <label class="form-label" for="tanggal">Tanggal</label>
            <input class="form-control" id="tanggal" name="tanggal" type="date" value="{{ $tanggal }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">Tampilkan</button>
            @if ($idBarang || $idPenerimaan || $jenis || $tanggal)
                <a class="btn btn-outline-secondary" href="{{ route('kartu-stok.index') }}">Reset</a>
            @endif
        </div>
    </form>

    @if ($barangDipilih)
        <p>Stok saat ini: <strong>{{ $barangDipilih->stok }}</strong></p>
    @endif
    @if ($idPenerimaan && Gate::allows('penerimaan.baca'))
        <p><a href="{{ route('penerimaan.show', $idPenerimaan) }}">Penerimaan #{{ $idPenerimaan }}</a></p>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Barang</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Transaksi</th>
                    <th scope="col">Masuk</th>
                    <th scope="col">Keluar</th>
                    <th scope="col">Saldo</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </div>

    {{ $mutasi->links('pagination::simple-bootstrap-5') }}
@endsection

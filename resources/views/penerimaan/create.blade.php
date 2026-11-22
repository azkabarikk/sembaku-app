@extends('layouts.app')

@section('title', 'Tambah Penerimaan')
@section('heading', 'Penerimaan Barang')
@section('description', 'Catat barang yang diterima dari pengadaan.')

@section('content')
    @if ($errors->has('jumlah_terima'))
        <div class="alert alert-danger">{{ $errors->first('jumlah_terima') }}</div>
    @endif
    @error('penerimaan')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="card mb-4">
        <div class="card-body">
            <div><strong>Pengadaan:</strong> #{{ $pengadaan->idpengadaan }}</div>
            <div><strong>Vendor:</strong> {{ $pengadaan->nama_vendor ?? '—' }}</div>
            <div><strong>Tanggal:</strong> {{ $pengadaan->dibuat }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('penerimaan.store', $pengadaan->idpengadaan) }}">
        @csrf
        <input type="hidden" name="penerimaan_terakhir" value="{{ $idTerakhir }}">

        <p class="text-muted mb-2">Kosongkan harga untuk memakai harga yang tercatat pada pengadaan.</p>

        <div class="table-responsive mb-3">
            <table class="table table-striped table-bordered align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Barang</th>
                        <th scope="col">Dipesan</th>
                        <th scope="col">Sudah Diterima</th>
                        <th scope="col">Draf Lain</th>
                        <th scope="col">Sisa</th>
                        <th scope="col">Diterima Sekarang</th>
                        <th scope="col">Harga Satuan Terima (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barisPesanan as $baris)
                        <tr>
                            <td>{{ $baris->nama_barang }}</td>
                            <td>{{ $baris->jumlah_dipesan }}</td>
                            <td>{{ $baris->jumlah_diterima }}</td>
                            <td>{{ $baris->jumlah_draf }}</td>
                            <td>{{ $baris->jumlah_sisa }}</td>
                            <td>
                                <input
                                    class="form-control @error('jumlah_terima.' . $baris->idbarang) is-invalid @enderror"
                                    name="jumlah_terima[{{ $baris->idbarang }}]"
                                    type="number"
                                    min="0"
                                    max="{{ $baris->jumlah_sisa }}"
                                    value="{{ old('jumlah_terima.' . $baris->idbarang, 0) }}"
                                    required
                                >
                                @error('jumlah_terima.' . $baris->idbarang)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input
                                    class="form-control @error('harga_satuan_terima.' . $baris->idbarang) is-invalid @enderror"
                                    name="harga_satuan_terima[{{ $baris->idbarang }}]"
                                    aria-label="Harga satuan {{ $baris->nama_barang }}"
                                    type="number"
                                    min="0"
                                    max="2147483647"
                                    step="1"
                                    placeholder="{{ $baris->harga_pesanan }}"
                                    value="{{ old('harga_satuan_terima.' . $baris->idbarang) }}"
                                >
                                @error('harga_satuan_terima.' . $baris->idbarang)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Penerimaan</button>
            <a class="btn btn-secondary" href="{{ route('penerimaan.index') }}">Batal</a>
        </div>
    </form>
@endsection

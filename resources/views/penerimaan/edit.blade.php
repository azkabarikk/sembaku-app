@extends('layouts.app')

@section('title', 'Edit Penerimaan')
@section('heading', 'Edit Penerimaan')
@section('description', 'Perbarui jumlah dan harga pada draf penerimaan.')

@section('content')
    @if ($errors->has('penerimaan'))
        <div class="alert alert-danger">{{ $errors->first('penerimaan') }}</div>
    @endif
    @if ($errors->has('jumlah_terima'))
        <div class="alert alert-danger">{{ $errors->first('jumlah_terima') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div><strong>Penerimaan:</strong> #{{ $penerimaan->idpenerimaan }}</div>
            <div><strong>Pengadaan:</strong> #{{ $penerimaan->idpengadaan }}</div>
            <div><strong>Tanggal:</strong> {{ $penerimaan->created_at }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('penerimaan.update', $penerimaan->idpenerimaan) }}">
        @csrf
        @method('PUT')

        <div class="table-responsive mb-3">
            <table class="table table-striped table-bordered align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Barang</th>
                        <th scope="col">Dipesan</th>
                        <th scope="col">Diterima di Penerimaan Lain</th>
                        <th scope="col">Draf Lain</th>
                        <th scope="col">Batas untuk Draf Ini</th>
                        <th scope="col">Jumlah Diterima</th>
                        <th scope="col">Harga Satuan Terima (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barisPesanan as $baris)
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
                                    value="{{ old('jumlah_terima.' . $baris->idbarang, $rincianDraf[$baris->idbarang]->jumlah_terima ?? 0) }}"
                                    required
                                >
                                @error('jumlah_terima.' . $baris->idbarang)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input
                                    type="number"
                                    class="form-control @error('harga_satuan_terima.' . $baris->idbarang) is-invalid @enderror"
                                    name="harga_satuan_terima[{{ $baris->idbarang }}]"
                                    aria-label="Harga satuan {{ $baris->nama_barang }}"
                                    min="0"
                                    max="2147483647"
                                    step="1"
                                    placeholder="{{ $baris->harga_pesanan }}"
                                    value="{{ old('harga_satuan_terima.' . $baris->idbarang, $rincianDraf[$baris->idbarang]->harga_satuan_terima ?? null) }}"
                                >
                                @error('harga_satuan_terima.' . $baris->idbarang)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada barang yang masih dapat diterima.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            <a class="btn btn-secondary" href="{{ route('penerimaan.index') }}">Kembali</a>
        </div>
    </form>
@endsection

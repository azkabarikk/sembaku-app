@extends('layouts.app')

@section('title', 'Edit Barang')
@section('heading', 'Edit Barang')
@section('description', 'Perbarui informasi barang.')

@section('content')
    <form method="POST" action="{{ route('barang.update', $barang->idbarang) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label" for="nama">Nama Barang</label>
            <input
                class="form-control @error('nama') is-invalid @enderror"
                id="nama"
                name="nama"
                type="text"
                maxlength="45"
                value="{{ old('nama', $barang->nama) }}"
                required
            >
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="jenis">Jenis</label>
            <select class="form-select @error('jenis') is-invalid @enderror" id="jenis" name="jenis" required>
                <option value="S" @selected(old('jenis', $barang->jenis) === 'S')>Sembako</option>
                <option value="R" @selected(old('jenis', $barang->jenis) === 'R')>Rumah Tangga</option>
            </select>
            @error('jenis')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="idsatuan">Satuan</label>
            <select class="form-select @error('idsatuan') is-invalid @enderror" id="idsatuan" name="idsatuan" required>
                @foreach ($daftarSatuan as $satuan)
                    <option value="{{ $satuan->idsatuan }}" @selected(old('idsatuan', $barang->idsatuan) == $satuan->idsatuan)>
                        {{ $satuan->nama_satuan }}{{ $satuan->status == 1 ? '' : ' (Tidak Aktif)' }}
                    </option>
                @endforeach
            </select>
            @error('idsatuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="harga">Harga</label>
            <input
                class="form-control @error('harga') is-invalid @enderror"
                id="harga"
                name="harga"
                type="number"
                min="0"
                step="1"
                value="{{ old('harga', $barang->harga) }}"
                required
            >
            @error('harga')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="1" @selected(old('status', $barang->status) == 1)>Aktif</option>
                <option value="0" @selected(old('status', $barang->status) == 0)>Tidak Aktif</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            <a class="btn btn-secondary" href="{{ route('barang.index') }}">Batal</a>
        </div>
    </form>
@endsection

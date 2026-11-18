@extends('layouts.app')

@section('title', 'Tambah Satuan')
@section('heading', 'Tambah Satuan')
@section('description', 'Isi nama satuan yang akan digunakan untuk barang.')

@section('content')
    <form method="POST" action="{{ route('satuan.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="nama_satuan">Nama Satuan</label>
            <input
                class="form-control @error('nama_satuan') is-invalid @enderror"
                id="nama_satuan"
                name="nama_satuan"
                type="text"
                maxlength="45"
                value="{{ old('nama_satuan') }}"
                required
            >
            @error('nama_satuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                <option value="0" @selected(old('status') === '0')>Tidak Aktif</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('satuan.index') }}">Batal</a>
        </div>
    </form>
@endsection

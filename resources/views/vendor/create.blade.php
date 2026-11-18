@extends('layouts.app')

@section('title', 'Tambah Vendor')
@section('heading', 'Tambah Vendor')
@section('description', 'Isi informasi vendor pemasok.')

@section('content')
    <form method="POST" action="{{ route('vendor.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="nama_vendor">Nama Vendor</label>
            <input
                class="form-control @error('nama_vendor') is-invalid @enderror"
                id="nama_vendor"
                name="nama_vendor"
                type="text"
                maxlength="100"
                value="{{ old('nama_vendor') }}"
                required
            >
            @error('nama_vendor')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="badan_hukum">Badan Hukum</label>
            <select class="form-select @error('badan_hukum') is-invalid @enderror" id="badan_hukum" name="badan_hukum" required>
                <option value="">Pilih bentuk badan hukum</option>
                <option value="B" @selected(old('badan_hukum') === 'B')>Berbadan Hukum</option>
                <option value="N" @selected(old('badan_hukum') === 'N')>Tidak Berbadan Hukum</option>
            </select>
            @error('badan_hukum')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="A" @selected(old('status', 'A') === 'A')>Aktif</option>
                <option value="N" @selected(old('status') === 'N')>Tidak Aktif</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('vendor.index') }}">Batal</a>
        </div>
    </form>
@endsection

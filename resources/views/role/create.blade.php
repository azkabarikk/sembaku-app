@extends('layouts.app')

@section('title', 'Tambah Role')
@section('heading', 'Tambah Role')
@section('description', 'Isi nama role yang akan digunakan oleh user.')

@section('content')
    <form method="POST" action="{{ route('role.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="nama_role">Nama Role</label>
            <input
                class="form-control @error('nama_role') is-invalid @enderror"
                id="nama_role"
                name="nama_role"
                type="text"
                maxlength="100"
                value="{{ old('nama_role') }}"
                required
            >
            @error('nama_role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('role.index') }}">Batal</a>
        </div>
    </form>
@endsection

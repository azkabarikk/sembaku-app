@extends('layouts.app')

@section('title', 'Edit Role')
@section('heading', 'Edit Role')
@section('description', 'Perbarui nama role.')

@section('content')
    <form method="POST" action="{{ route('role.update', $role->idrole) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label" for="nama_role">Nama Role</label>
            <input
                class="form-control @error('nama_role') is-invalid @enderror"
                id="nama_role"
                name="nama_role"
                type="text"
                maxlength="100"
                value="{{ old('nama_role', $role->nama_role) }}"
                required
            >
            @error('nama_role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            <a class="btn btn-secondary" href="{{ route('role.index') }}">Batal</a>
        </div>
    </form>
@endsection

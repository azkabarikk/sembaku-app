@extends('layouts.app')

@section('title', 'Tambah User')
@section('heading', 'Tambah User')
@section('description', 'Isi username, password, dan role untuk akun baru.')

@section('content')
    <form method="POST" action="{{ route('user.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input
                class="form-control @error('username') is-invalid @enderror"
                id="username"
                name="username"
                type="text"
                maxlength="45"
                autocomplete="username"
                value="{{ old('username') }}"
                required
            >
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                required
            >
            <div class="form-text">Minimal 8 karakter.</div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input
                class="form-control"
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label" for="idrole">Role</label>
            <select class="form-select @error('idrole') is-invalid @enderror" id="idrole" name="idrole" required>
                <option value="">Pilih role</option>
                @foreach ($daftarRole as $role)
                    <option value="{{ $role->idrole }}" @selected(old('idrole') == $role->idrole)>
                        {{ $role->nama_role }}
                    </option>
                @endforeach
            </select>
            @error('idrole')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-secondary" href="{{ route('user.index') }}">Batal</a>
        </div>
    </form>
@endsection

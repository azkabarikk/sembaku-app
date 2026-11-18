@extends('layouts.app')

@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('description', 'Perbarui username, role, atau ganti password.')

@section('content')
    <form method="POST" action="{{ route('user.update', $user->iduser) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input
                class="form-control @error('username') is-invalid @enderror"
                id="username"
                name="username"
                type="text"
                maxlength="45"
                autocomplete="username"
                value="{{ old('username', $user->username) }}"
                required
            >
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Password baru</label>
            <input
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
            >
            <div class="form-text">Kosongkan jika tidak ingin mengganti password.</div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
            <input
                class="form-control"
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
            >
        </div>

        <div class="mb-3">
            <label class="form-label" for="idrole">Role</label>
            <select class="form-select @error('idrole') is-invalid @enderror" id="idrole" name="idrole" required>
                @foreach ($daftarRole as $role)
                    <option value="{{ $role->idrole }}" @selected(old('idrole', $user->idrole) == $role->idrole)>
                        {{ $role->nama_role }}
                    </option>
                @endforeach
            </select>
            @error('idrole')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            <a class="btn btn-secondary" href="{{ route('user.index') }}">Batal</a>
        </div>
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Tambah User')
@section('heading', 'Tambah User')
@section('description', 'Isi username, password, dan role untuk akun baru.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('user.store') }}">
            @csrf

            <x-ui.text-field label="Username" id="username" name="username" type="text" maxlength="45" autocomplete="username" required />

            <x-ui.text-field label="Password" id="password" name="password" type="password" autocomplete="new-password" required helper="Minimal 8 karakter." />

            <x-ui.text-field label="Konfirmasi Password" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required />

            <x-ui.select label="Role" id="idrole" name="idrole" required>
                <option value="">Pilih role</option>
                @foreach ($daftarRole as $role)
                    <option value="{{ $role->idrole }}" @selected(old('idrole') == $role->idrole)>
                        {{ $role->nama_role }}
                    </option>
                @endforeach
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('user.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

@extends('layouts.app')

@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('description', 'Perbarui username, role, atau ganti password.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('user.update', $user->iduser) }}">
            @csrf
            @method('PUT')

            <x-ui.text-field label="Username" id="username" name="username" type="text" maxlength="45" autocomplete="username" :value="$user->username" required />

            <x-ui.text-field label="Password baru" id="password" name="password" type="password" autocomplete="new-password" helper="Kosongkan jika tidak ingin mengganti password." />

            <x-ui.text-field label="Konfirmasi Password Baru" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />

            <x-ui.select label="Role" id="idrole" name="idrole" required>
                @foreach ($daftarRole as $role)
                    <option value="{{ $role->idrole }}" @selected(old('idrole', $user->idrole) == $role->idrole)>
                        {{ $role->nama_role }}
                    </option>
                @endforeach
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan Perubahan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('user.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

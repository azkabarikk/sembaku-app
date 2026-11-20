@extends('layouts.app')

@section('title', 'Edit Role')
@section('heading', 'Edit Role')
@section('description', 'Perbarui nama role.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('role.update', $role->idrole) }}">
            @csrf
            @method('PUT')

            <x-ui.text-field label="Nama Role" id="nama_role" name="nama_role" type="text" maxlength="100" :value="$role->nama_role" required />

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan Perubahan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('role.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

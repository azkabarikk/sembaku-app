@extends('layouts.app')

@section('title', 'Tambah Role')
@section('heading', 'Tambah Role')
@section('description', 'Isi nama role yang akan digunakan oleh user.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('role.store') }}">
            @csrf

            <x-ui.text-field label="Nama Role" id="nama_role" name="nama_role" type="text" maxlength="100" required />

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('role.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

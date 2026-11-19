@extends('layouts.app')

@section('title', 'Tambah Barang')
@section('heading', 'Tambah Barang')
@section('description', 'Isi informasi barang yang akan ditambahkan.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form method="POST" action="{{ route('barang.store') }}" class="md3-stack">
            @csrf

            @include('barang.form-fields', ['barang' => null, 'hargaTerkunci' => false])

            <div class="md3-cluster">
                <x-ui.button type="submit">Simpan</x-ui.button>
                <x-ui.button variant="outlined" :href="route('barang.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

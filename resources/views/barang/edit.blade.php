@extends('layouts.app')

@section('title', 'Edit Barang')
@section('heading', 'Edit Barang')
@section('description', 'Perbarui informasi barang.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form method="POST" action="{{ route('barang.update', $barang->idbarang) }}" class="md3-stack">
            @csrf
            @method('PUT')

            @include('barang.form-fields', ['barang' => $barang, 'hargaTerkunci' => $hargaTerkunci])

            <div class="md3-cluster">
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
                <x-ui.button variant="outlined" :href="route('barang.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

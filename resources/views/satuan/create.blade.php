@extends('layouts.app')

@section('title', 'Tambah Satuan')
@section('heading', 'Tambah Satuan')
@section('description', 'Isi nama satuan yang akan digunakan untuk barang.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('satuan.store') }}">
            @csrf

            <x-ui.text-field label="Nama Satuan" id="nama_satuan" name="nama_satuan" type="text" maxlength="45" required />

            <x-ui.select label="Status" id="status" name="status" required>
                <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                <option value="0" @selected(old('status') === '0')>Tidak Aktif</option>
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('satuan.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

@extends('layouts.app')

@section('title', 'Tambah Margin Penjualan')
@section('heading', 'Tambah Margin Penjualan')
@section('description', 'Isi persentase margin dan pilih statusnya.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('margin-penjualan.store') }}">
            @csrf

            <x-ui.text-field label="Persentase (%)" id="persen" name="persen" type="number" min="0" max="100" step="0.01" required />

            <x-ui.select label="Status" id="status" name="status" required>
                <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                <option value="0" @selected(old('status') === '0')>Tidak Aktif</option>
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('margin-penjualan.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

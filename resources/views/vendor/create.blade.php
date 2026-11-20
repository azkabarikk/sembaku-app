@extends('layouts.app')

@section('title', 'Tambah Vendor')
@section('heading', 'Tambah Vendor')
@section('description', 'Isi informasi vendor pemasok.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('vendor.store') }}">
            @csrf

            <x-ui.text-field label="Nama Vendor" id="nama_vendor" name="nama_vendor" type="text" maxlength="100" required />

            <x-ui.select label="Badan Hukum" id="badan_hukum" name="badan_hukum" required>
                <option value="">Pilih bentuk badan hukum</option>
                <option value="B" @selected(old('badan_hukum') === 'B')>Berbadan Hukum</option>
                <option value="N" @selected(old('badan_hukum') === 'N')>Tidak Berbadan Hukum</option>
            </x-ui.select>

            <x-ui.select label="Status" id="status" name="status" required>
                <option value="A" @selected(old('status', 'A') === 'A')>Aktif</option>
                <option value="N" @selected(old('status') === 'N')>Tidak Aktif</option>
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('vendor.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

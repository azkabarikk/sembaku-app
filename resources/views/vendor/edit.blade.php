@extends('layouts.app')

@section('title', 'Edit Vendor')
@section('heading', 'Edit Vendor')
@section('description', 'Perbarui informasi vendor.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('vendor.update', $vendor->idvendor) }}">
            @csrf
            @method('PUT')

            <x-ui.text-field label="Nama Vendor" id="nama_vendor" name="nama_vendor" type="text" maxlength="100" :value="$vendor->nama_vendor" required />

            <x-ui.select label="Badan Hukum" id="badan_hukum" name="badan_hukum" required>
                <option value="B" @selected(old('badan_hukum', $vendor->badan_hukum) === 'B')>Berbadan Hukum</option>
                <option value="N" @selected(old('badan_hukum', $vendor->badan_hukum) === 'N')>Tidak Berbadan Hukum</option>
            </x-ui.select>

            <x-ui.select label="Status" id="status" name="status" required>
                <option value="A" @selected(old('status', $vendor->status) === 'A')>Aktif</option>
                <option value="N" @selected(old('status', $vendor->status) === 'N')>Tidak Aktif</option>
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan Perubahan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('vendor.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

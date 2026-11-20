@extends('layouts.app')

@section('title', 'Edit Satuan')
@section('heading', 'Edit Satuan')
@section('description', 'Perbarui nama atau status satuan.')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('satuan.update', $satuan->idsatuan) }}">
            @csrf
            @method('PUT')

            <x-ui.text-field label="Nama Satuan" id="nama_satuan" name="nama_satuan" type="text" maxlength="45" :value="$satuan->nama_satuan" required />

            <x-ui.select label="Status" id="status" name="status" required>
                <option value="1" @selected(old('status', $satuan->status) == 1)>Aktif</option>
                <option value="0" @selected(old('status', $satuan->status) == 0)>Tidak Aktif</option>
            </x-ui.select>

            <div class="md3-cluster">
                <x-ui.button variant="filled" type="submit">Simpan Perubahan</x-ui.button>
                <x-ui.button variant="tonal" :href="route('satuan.index')">Batal</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection

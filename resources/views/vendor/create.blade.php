@extends('layouts.app')

@section('title', 'Tambah Vendor')
@section('heading', 'Tambah Vendor')
@section('description', 'Isi informasi vendor pemasok.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('vendor.store') }}">
        @csrf

        <x-ui.form-section title="Informasi vendor" icon="store">
            <div class="md3-form-grid">
                <div class="md3-form-grid__wide">
                    <x-ui.text-field label="Nama Vendor" id="nama_vendor" name="nama_vendor" type="text" placeholder="Nama pemasok" maxlength="100" required />
                </div>

                <x-ui.select label="Badan Hukum" id="badan_hukum" name="badan_hukum" required>
                    <option value="">Pilih bentuk badan hukum</option>
                    <option value="B" @selected(old('badan_hukum') === 'B')>Berbadan Hukum</option>
                    <option value="N" @selected(old('badan_hukum') === 'N')>Tidak Berbadan Hukum</option>
                </x-ui.select>

                <x-ui.select label="Status" id="status" name="status" required>
                    <option value="A" @selected(old('status', 'A') === 'A')>Aktif</option>
                    <option value="N" @selected(old('status') === 'N')>Tidak Aktif</option>
                </x-ui.select>
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('vendor.index')" />
    </form>
@endsection

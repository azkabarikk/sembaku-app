@extends('layouts.app')

@section('title', 'Edit Satuan')
@section('heading', 'Edit Satuan')
@section('description', 'Perbarui nama atau status satuan.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('satuan.update', $satuan->idsatuan) }}">
        @csrf
        @method('PUT')

        <x-ui.form-section title="Informasi satuan" icon="scale">
            <div class="md3-form-grid">
                <x-ui.text-field label="Nama Satuan" id="nama_satuan" name="nama_satuan" type="text" placeholder="Contoh: Dus" maxlength="45" :value="$satuan->nama_satuan" required />

                <x-ui.select label="Status" id="status" name="status" required>
                    <option value="1" @selected(old('status', $satuan->status) == 1)>Aktif</option>
                    <option value="0" @selected(old('status', $satuan->status) == 0)>Tidak Aktif</option>
                </x-ui.select>
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('satuan.index')" submit="Simpan Perubahan" />
    </form>
@endsection

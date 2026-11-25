@extends('layouts.app')

@section('title', 'Tambah Satuan')
@section('heading', 'Tambah Satuan')
@section('description', 'Isi nama satuan yang akan digunakan untuk barang.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('satuan.store') }}">
        @csrf

        <x-ui.form-section title="Informasi satuan" icon="scale">
            <div class="md3-form-grid">
                <x-ui.text-field label="Nama Satuan" id="nama_satuan" name="nama_satuan" type="text" placeholder="Contoh: Dus" maxlength="45" required />

                <x-ui.select label="Status" id="status" name="status" required>
                    <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                    <option value="0" @selected(old('status') === '0')>Tidak Aktif</option>
                </x-ui.select>
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('satuan.index')" />
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Tambah Margin Penjualan')
@section('heading', 'Tambah Margin Penjualan')
@section('description', 'Isi persentase margin dan pilih statusnya.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('margin-penjualan.store') }}">
        @csrf

        <x-ui.form-section title="Pengaturan margin" icon="percent">
            <div class="md3-form-grid">
                <x-ui.text-field label="Persentase (%)" id="persen" name="persen" type="number" inputmode="decimal" leading-icon="percent" helper="Masukkan nilai antara 0 dan 100." min="0" max="100" step="0.01" required />

                <x-ui.select label="Status" id="status" name="status" required>
                    <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                    <option value="0" @selected(old('status') === '0')>Tidak Aktif</option>
                </x-ui.select>
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('margin-penjualan.index')" />
    </form>
@endsection

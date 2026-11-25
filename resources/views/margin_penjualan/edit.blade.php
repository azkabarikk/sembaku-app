@extends('layouts.app')

@section('title', 'Edit Margin Penjualan')
@section('heading', 'Edit Margin Penjualan')
@section('description', 'Perbarui persentase atau status margin.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('margin-penjualan.update', $margin->idmargin_penjualan) }}">
        @csrf
        @method('PUT')

        <x-ui.form-section title="Pengaturan margin" icon="percent">
            <div class="md3-form-grid">
                <x-ui.text-field label="Persentase (%)" id="persen" name="persen" type="number" inputmode="decimal" leading-icon="percent" helper="Masukkan nilai antara 0 dan 100." min="0" max="100" step="0.01" :value="$margin->persen" required />

                <x-ui.select label="Status" id="status" name="status" required>
                    <option value="1" @selected(old('status', $margin->status) == 1)>Aktif</option>
                    <option value="0" @selected(old('status', $margin->status) == 0)>Tidak Aktif</option>
                </x-ui.select>
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('margin-penjualan.index')" submit="Simpan Perubahan" />
    </form>
@endsection

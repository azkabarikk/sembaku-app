@extends('layouts.app')

@section('title', 'Edit Barang')
@section('heading', 'Edit Barang')
@section('description', 'Perbarui informasi barang.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('barang.update', $barang->idbarang) }}">
        @csrf
        @method('PUT')

        <x-ui.form-section title="Detail barang" icon="package">
            @include('barang.form-fields', ['barang' => $barang, 'hargaTerkunci' => $hargaTerkunci])
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('barang.index')" submit="Simpan Perubahan" />
    </form>
@endsection

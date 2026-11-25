@extends('layouts.app')

@section('title', 'Tambah Barang')
@section('heading', 'Tambah Barang')
@section('description', 'Isi informasi barang yang akan ditambahkan.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('barang.store') }}">
        @csrf

        <x-ui.form-section title="Detail barang" icon="package">
            @include('barang.form-fields', ['barang' => null, 'hargaTerkunci' => false])
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('barang.index')" />
    </form>
@endsection

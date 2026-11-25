@extends('layouts.app')

@section('title', 'Tambah Penerimaan')
@section('heading', 'Penerimaan Barang')
@section('description', 'Catat barang yang diterima dari pengadaan.')

@section('content')
    @if ($errors->has('jumlah_terima'))
        <x-ui.alert variant="error">{{ $errors->first('jumlah_terima') }}</x-ui.alert>
    @endif
    @error('penerimaan')
        <x-ui.alert variant="error">{{ $message }}</x-ui.alert>
    @enderror

    <x-ui.form-section title="Sumber penerimaan" icon="clipboard-list">
        <dl class="md3-form-meta">
            <div><dt>Pengadaan</dt><dd>#{{ $pengadaan->idpengadaan }}</dd></div>
            <div><dt>Vendor</dt><dd>{{ $pengadaan->nama_vendor ?? '—' }}</dd></div>
            <div><dt>Tanggal</dt><dd>{{ $pengadaan->dibuat }}</dd></div>
        </dl>
    </x-ui.form-section>

    <form class="md3-form" method="POST" action="{{ route('penerimaan.store', $pengadaan->idpengadaan) }}">
        @csrf
        <input type="hidden" name="penerimaan_terakhir" value="{{ $idTerakhir }}">

        @include('penerimaan._items', ['sedangEdit' => false])

        <x-ui.form-actions :cancel="route('penerimaan.index')" submit="Simpan Penerimaan" />
    </form>
@endsection

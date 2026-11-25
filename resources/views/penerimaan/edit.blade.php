@extends('layouts.app')

@section('title', 'Edit Penerimaan')
@section('heading', 'Edit Penerimaan')
@section('description', 'Periksa jumlah dan harga. Menyimpan draf akan menambah stok dan mengunci penerimaan.')

@section('content')
    @if ($errors->has('penerimaan'))
        <x-ui.alert variant="error">{{ $errors->first('penerimaan') }}</x-ui.alert>
    @endif
    @if ($errors->has('jumlah_terima'))
        <x-ui.alert variant="error">{{ $errors->first('jumlah_terima') }}</x-ui.alert>
    @endif

    <x-ui.form-section title="Sumber penerimaan" icon="clipboard-list">
        <dl class="md3-form-meta">
            <div><dt>Penerimaan</dt><dd>#{{ $penerimaan->idpenerimaan }}</dd></div>
            <div><dt>Pengadaan</dt><dd>#{{ $penerimaan->idpengadaan }}</dd></div>
            <div><dt>Tanggal</dt><dd>{{ $penerimaan->created_at }}</dd></div>
        </dl>
    </x-ui.form-section>

    <form class="md3-form" method="POST" action="{{ route('penerimaan.update', $penerimaan->idpenerimaan) }}">
        @csrf
        @method('PUT')

        @include('penerimaan._items', ['sedangEdit' => true])

        <x-ui.form-actions :cancel="route('penerimaan.index')" submit="Simpan Penerimaan" cancel-label="Kembali" />
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Beranda')
@section('heading', 'Sistem Persediaan SembaKu')
@section('description', 'Pilih menu untuk melihat data master.')

@section('content')
    <p>
        <a class="btn btn-primary" href="{{ route('barang.index') }}">Lihat Data Barang</a>
    </p>
@endsection

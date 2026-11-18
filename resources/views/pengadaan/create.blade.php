@extends('layouts.app')

@section('title', 'Tambah Pengadaan')
@section('heading', 'Tambah Pengadaan')
@section('description', 'Isi vendor dan barang yang akan dipesan.')

@section('content')
    @include('pengadaan._form')
@endsection

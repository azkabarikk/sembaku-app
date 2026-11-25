@extends('layouts.app')

@section('title', 'Tambah User')
@section('heading', 'Tambah User')
@section('description', 'Isi username, password, dan role untuk akun baru.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('user.store') }}">
        @csrf

        @include('user._fields', ['user' => null])

        <x-ui.form-actions :cancel="route('user.index')" />
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('description', 'Perbarui username, role, atau ganti password.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('user.update', $user->iduser) }}">
        @csrf
        @method('PUT')

        @include('user._fields', ['user' => $user])

        <x-ui.form-actions :cancel="route('user.index')" submit="Simpan Perubahan" />
    </form>
@endsection

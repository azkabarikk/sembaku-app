@extends('layouts.app')

@section('title', 'Tambah Role')
@section('heading', 'Tambah Role')
@section('description', 'Isi nama role yang akan digunakan oleh user.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('role.store') }}">
        @csrf

        <x-ui.form-section title="Identitas role" icon="shield">
            <div class="md3-form-grid">
                <x-ui.text-field label="Nama Role" id="nama_role" name="nama_role" type="text" placeholder="Contoh: Petugas Gudang" maxlength="100" required />
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('role.index')" />
    </form>
@endsection

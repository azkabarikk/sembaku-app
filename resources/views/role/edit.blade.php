@extends('layouts.app')

@section('title', 'Edit Role')
@section('heading', 'Edit Role')
@section('description', 'Perbarui nama role.')

@section('content')
    <form class="md3-form md3-form--standard" method="POST" action="{{ route('role.update', $role->idrole) }}">
        @csrf
        @method('PUT')

        <x-ui.form-section title="Identitas role" icon="shield">
            <div class="md3-form-grid">
                <x-ui.text-field label="Nama Role" id="nama_role" name="nama_role" type="text" placeholder="Contoh: Petugas Gudang" maxlength="100" :value="$role->nama_role" required />
            </div>
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('role.index')" submit="Simpan Perubahan" />
    </form>
@endsection

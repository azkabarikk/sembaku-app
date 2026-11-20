@extends('layouts.app')

@section('title', 'Masuk')
@section('heading', 'Masuk')

@section('content')
    <x-ui.card variant="outlined" class="md3-form-card">
        <form class="md3-stack" method="POST" action="{{ route('login.store') }}">
            @csrf

            <x-ui.text-field label="Username" id="username" name="username" autocomplete="username" required />

            <x-ui.text-field label="Password" id="password" name="password" type="password" autocomplete="current-password" required />

            <x-ui.button type="submit">Masuk</x-ui.button>
        </form>
    </x-ui.card>
@endsection

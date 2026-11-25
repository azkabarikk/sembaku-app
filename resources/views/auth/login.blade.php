@extends('layouts.app')

@section('title', 'Masuk')
@section('content')
    <x-ui.card variant="outlined" class="md3-login-card">
        <x-slot:header>
            <div class="md3-login-heading">
                <div class="md3-login-icon"><x-ui.icon name="shopping-basket" /></div>
                <h1 class="md3-type-headline-small">Masuk ke SembaKu</h1>
            </div>
        </x-slot:header>

        <form class="md3-stack" method="POST" action="{{ route('login.store') }}">
            @csrf

            <x-ui.text-field label="Username" id="username" name="username" autocomplete="username" required />

            <x-ui.text-field label="Password" id="password" name="password" type="password" autocomplete="current-password" required />

            <x-ui.button type="submit" size="large" :full-width="true">Masuk</x-ui.button>
        </form>
    </x-ui.card>
@endsection

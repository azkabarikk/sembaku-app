@extends('layouts.app')

@section('title', 'Masuk')
@section('content')
    <section class="md3-login" aria-labelledby="login-title">
        <div class="md3-login__hero">
            <div class="md3-login__art" aria-hidden="true">
                <span class="md3-login__orbit"></span>
                <span class="md3-login__tile md3-login__tile--stock"><x-ui.icon name="clipboard-list" /></span>
                <span class="md3-login__tile md3-login__tile--main"><x-ui.icon name="package" /></span>
                <span class="md3-login__tile md3-login__tile--check"><x-ui.icon name="check" /></span>
            </div>
            <div class="md3-login__hero-copy">
                <p class="md3-type-display-small">Persediaan dalam satu tempat</p>
                <p>Pantau stok dan kelola pengadaan, penerimaan, serta transaksi harian.</p>
            </div>
        </div>

        <div class="md3-login__body">
            <div class="md3-login__heading">
                <span class="md3-login__badge"><x-ui.icon name="lock" /></span>
                <h1 id="login-title" class="md3-type-headline-large">Masuk ke SembaKu</h1>
                <p class="md3-muted">Gunakan akun kerja untuk melanjutkan.</p>
            </div>

            <form class="md3-stack md3-login__form" method="POST" action="{{ route('login.store') }}">
                @csrf

                <x-ui.text-field label="Username" id="username" name="username" leading-icon="person"
                    autocomplete="username" autocapitalize="none" spellcheck="false" required />

                <x-ui.password-field id="password" name="password" autocomplete="current-password"
                    data-caps-lock="login-caps-lock" required />
                <p id="login-caps-lock" class="md3-login__caps md3-type-label-large" role="status" hidden>
                    <x-ui.icon name="warning" /> Caps Lock aktif.
                </p>

                <x-ui.button type="submit" size="large" icon-name="login" full-width>Masuk</x-ui.button>
            </form>
        </div>
    </section>
@endsection

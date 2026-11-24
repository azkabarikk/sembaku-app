<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SembaKu</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="app-layout">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark app-sidebar">
            <div class="container-fluid flex-column align-items-stretch p-3">
                <a class="navbar-brand" href="{{ url('/') }}">SembaKu</a>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNavigation"
                    aria-controls="mainNavigation"
                    aria-expanded="false"
                    aria-label="Buka navigasi"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavigation">
                    <ul class="navbar-nav w-100">
                        @can('administrasi')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('satuan.*') ? 'active' : '' }}" href="{{ route('satuan.index') }}">Satuan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vendor.*') ? 'active' : '' }}" href="{{ route('vendor.index') }}">Vendor</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('role.*') ? 'active' : '' }}" href="{{ route('role.index') }}">Role</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">User</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('margin-penjualan.*') ? 'active' : '' }}" href="{{ route('margin-penjualan.index') }}">Margin Penjualan</a>
                            </li>
                        @endcan
                        <li class="sidebar-divider" aria-hidden="true"></li>
                        @can('pengadaan.baca')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pengadaan.*') ? 'active' : '' }}" href="{{ route('pengadaan.index') }}">Pengadaan</a>
                            </li>
                        @endcan
                        @can('penerimaan.baca')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('penerimaan.*') ? 'active' : '' }}" href="{{ route('penerimaan.index') }}">Penerimaan</a>
                            </li>
                        @endcan
                        @can('penjualan.kelola')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('penjualan.*') ? 'active' : '' }}" href="{{ route('penjualan.index') }}">Penjualan</a>
                            </li>
                        @endcan
                        @can('administrasi')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('retur.*') ? 'active' : '' }}" href="{{ route('retur.index') }}">Retur</a>
                            </li>
                        @endcan
                        @can('kartu-stok.baca')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('kartu-stok.*') ? 'active' : '' }}" href="{{ route('kartu-stok.index') }}">Kartu Stok</a>
                            </li>
                        @endcan
                        @can('stok-barang.baca')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('stok-barang.*') ? 'active' : '' }}" href="{{ route('stok-barang.index') }}">Stok Barang</a>
                            </li>
                        @endcan
                        @auth
                            <li class="nav-item">
                                <span class="nav-link d-flex align-items-center gap-2">
                                    <svg class="flex-shrink-0" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true" focusable="false">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <circle cx="12" cy="9" r="3"></circle>
                                        <path d="M5.5 19a7 7 0 0 1 13 0"></path>
                                    </svg>
                                    <span class="text-break">
                                        {{ auth()->user()->username }}
                                        <small class="d-block">{{ auth()->user()->nama_role }}</small>
                                    </span>
                                </span>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link">Keluar</button>
                                </form>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <main class="app-main flex-grow-1 px-3 px-lg-4 py-4">
            <div class="container-fluid p-0">
                <div class="mb-4">
                    <h1 class="h3 mb-1">@yield('heading')</h1>
                    <p class="text-muted mb-0">@yield('description')</p>
                </div>

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>

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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
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
                <ul class="navbar-nav me-auto">
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
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pengadaan.*') ? 'active' : '' }}" href="{{ route('pengadaan.index') }}">Pengadaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('penerimaan.*') ? 'active' : '' }}" href="{{ route('penerimaan.index') }}">Penerimaan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('penjualan.*') ? 'active' : '' }}" href="{{ route('penjualan.index') }}">Penjualan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('retur.*') ? 'active' : '' }}" href="{{ route('retur.index') }}">Retur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kartu-stok.*') ? 'active' : '' }}" href="{{ route('kartu-stok.index') }}">Kartu Stok</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">@yield('heading')</h1>
            <p class="text-muted mb-0">@yield('description')</p>
        </div>

        @yield('content')
    </main>
</body>
</html>

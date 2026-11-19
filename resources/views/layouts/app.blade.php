<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SembaKu</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="md3">
    <x-ui.button href="#konten-utama" class="md3-skip-link">Lewati navigasi</x-ui.button>

    <div @class(['md3-app-frame', 'md3-app-frame--guest' => auth()->guest()])>
        @auth
            <x-ui.app-sidebar id="mainNavigation">
                <x-slot:brand>
                    <a class="md3-app-brand md3-action md3-type-title-large" href="{{ url('/') }}">SembaKu</a>
                </x-slot:brand>

                @can('administrasi')
                    <div class="md3-sidebar__group-label md3-type-label-medium">Master</div>
                    <x-ui.navigation-item :href="route('barang.index')" :active="request()->routeIs('barang.*')">Barang</x-ui.navigation-item>
                    <x-ui.navigation-item :href="route('satuan.index')" :active="request()->routeIs('satuan.*')">Satuan</x-ui.navigation-item>
                    <x-ui.navigation-item :href="route('vendor.index')" :active="request()->routeIs('vendor.*')">Vendor</x-ui.navigation-item>
                    <x-ui.navigation-item :href="route('role.index')" :active="request()->routeIs('role.*')">Role</x-ui.navigation-item>
                    <x-ui.navigation-item :href="route('user.index')" :active="request()->routeIs('user.*')">User</x-ui.navigation-item>
                    <x-ui.navigation-item :href="route('margin-penjualan.index')" :active="request()->routeIs('margin-penjualan.*')">Margin Penjualan</x-ui.navigation-item>
                    <x-ui.divider class="md3-sidebar__divider" />
                @endcan

                @canany(['pengadaan.baca', 'penerimaan.baca', 'administrasi', 'kartu-stok.baca', 'stok-barang.baca'])
                    <div class="md3-sidebar__group-label md3-type-label-medium">Transaksi dan stok</div>
                @endcanany
                @can('pengadaan.baca')
                    <x-ui.navigation-item :href="route('pengadaan.index')" :active="request()->routeIs('pengadaan.*')">Pengadaan</x-ui.navigation-item>
                @endcan
                @can('penerimaan.baca')
                    <x-ui.navigation-item :href="route('penerimaan.index')" :active="request()->routeIs('penerimaan.*')">Penerimaan</x-ui.navigation-item>
                @endcan
                @can('administrasi')
                    <x-ui.navigation-item :href="route('penjualan.index')" :active="request()->routeIs('penjualan.*')">Penjualan</x-ui.navigation-item>
                    <x-ui.navigation-item :href="route('retur.index')" :active="request()->routeIs('retur.*')">Retur</x-ui.navigation-item>
                @endcan
                @can('kartu-stok.baca')
                    <x-ui.navigation-item :href="route('kartu-stok.index')" :active="request()->routeIs('kartu-stok.*')">Kartu Stok</x-ui.navigation-item>
                @endcan
                @can('stok-barang.baca')
                    <x-ui.navigation-item :href="route('stok-barang.index')" :active="request()->routeIs('stok-barang.*')">Stok Barang</x-ui.navigation-item>
                @endcan

                <x-slot:footer>
                    <x-ui.divider />
                    <x-ui.user-profile :username="auth()->user()->username" :role="auth()->user()->nama_role" />
                </x-slot:footer>
            </x-ui.app-sidebar>
        @endauth

        <div class="md3-app-content">
            <x-ui.app-topbar :title="auth()->check() ? 'Sistem Persediaan' : null">
                @guest
                    <x-slot:leading>
                        <a class="md3-app-brand md3-action md3-type-title-large" href="{{ url('/') }}">SembaKu</a>
                    </x-slot:leading>
                @endguest
                <x-slot:actions>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button variant="text" type="submit">Keluar</x-ui.button>
                        </form>
                    @else
                        <x-ui.button variant="tonal" :href="route('login')">Masuk</x-ui.button>
                    @endauth
                </x-slot:actions>
            </x-ui.app-topbar>

            <x-ui.page-container class="md3-stack" id="konten-utama" tabindex="-1">
                @hasSection('page-header')
                    @yield('page-header')
                @else
                    <x-ui.page-header
                        :title="html_entity_decode($__env->yieldContent('heading'), ENT_QUOTES | ENT_HTML5, 'UTF-8')"
                        :subtitle="html_entity_decode($__env->yieldContent('description'), ENT_QUOTES | ENT_HTML5, 'UTF-8')"
                    />
                @endif

                @yield('content')
            </x-ui.page-container>
        </div>
    </div>
</body>
</html>

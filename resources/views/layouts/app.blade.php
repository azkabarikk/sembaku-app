<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SembaKu</title>
    <script>
        try {
            const storedTheme = localStorage.getItem('sembaku.theme');
            if (storedTheme === 'dark' || (!storedTheme && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.dataset.theme = 'dark';
            }
        } catch {}
    </script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="md3" @if ($errors->any()) data-md3-validation-failed @endif>
    <x-ui.button href="#konten-utama" class="md3-skip-link">Lewati navigasi</x-ui.button>

    <x-ui.app-topbar class="md3-app-bar">
        <x-slot:leading>
            @auth
                <x-ui.icon-button label="Navigasi utama" icon="menu" class="md3-app-bar__menu"
                    aria-controls="mobileNavigation" aria-expanded="false" aria-haspopup="dialog" data-md3-drawer-trigger hidden />
            @endauth
            <a class="md3-app-brand md3-action md3-type-title-large" href="{{ url('/') }}">
                <span class="md3-app-brand__mark"><x-ui.icon name="shopping-basket" /></span>
                SembaKu
            </a>
        </x-slot:leading>
        <x-slot:actions>
            <x-ui.icon-button label="Aktifkan tema gelap" data-md3-theme hidden>
                <x-ui.icon name="moon" data-theme-icon="light" />
                <x-ui.icon name="sun" data-theme-icon="dark" hidden />
            </x-ui.icon-button>
            @auth
                <x-ui.user-profile :username="auth()->user()->username" :role="auth()->user()->nama_role" />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button variant="text" type="submit" icon-name="logout" class="md3-app-logout" title="Keluar" aria-label="Keluar">
                        <span>Keluar</span>
                    </x-ui.button>
                </form>
            @elseif (! request()->routeIs('login'))
                <x-ui.button variant="tonal" :href="route('login')" icon-name="login">Masuk</x-ui.button>
            @endauth
        </x-slot:actions>
    </x-ui.app-topbar>

    <div @class(['md3-app-frame', 'md3-app-frame--guest' => auth()->guest(), 'md3-app-frame--login' => request()->routeIs('login')])>
        @auth
            <x-ui.app-sidebar id="mainNavigation" :internal-toggle="false" data-md3-drawer-sidebar>
                <x-ui.navigation-item icon="home" :href="url('/')" :active="request()->is('/')">Beranda</x-ui.navigation-item>
                <x-ui.divider class="md3-sidebar__divider" />
                @can('administrasi')
                    <div class="md3-sidebar__group-label md3-type-label-medium">Master</div>
                    <x-ui.navigation-item icon="package" :href="route('barang.index')" :active="request()->routeIs('barang.*')">Barang</x-ui.navigation-item>
                    <x-ui.navigation-item icon="scale" :href="route('satuan.index')" :active="request()->routeIs('satuan.*')">Satuan</x-ui.navigation-item>
                    <x-ui.navigation-item icon="store" :href="route('vendor.index')" :active="request()->routeIs('vendor.*')">Vendor</x-ui.navigation-item>
                    <x-ui.navigation-item icon="shield" :href="route('role.index')" :active="request()->routeIs('role.*')">Role</x-ui.navigation-item>
                    <x-ui.navigation-item icon="person" :href="route('user.index')" :active="request()->routeIs('user.*')">User</x-ui.navigation-item>
                    <x-ui.navigation-item icon="percent" :href="route('margin-penjualan.index')" :active="request()->routeIs('margin-penjualan.*')">Margin Penjualan</x-ui.navigation-item>
                    <x-ui.divider class="md3-sidebar__divider" />
                @endcan

                @canany(['pengadaan.baca', 'penerimaan.baca', 'penjualan.kelola', 'retur.baca', 'kartu-stok.baca', 'stok-barang.baca'])
                    <div class="md3-sidebar__group-label md3-type-label-medium">Transaksi dan stok</div>
                @endcanany
                @can('pengadaan.baca')
                    <x-ui.navigation-item icon="shopping-cart" :href="route('pengadaan.index')" :active="request()->routeIs('pengadaan.*')">Pengadaan</x-ui.navigation-item>
                @endcan
                @can('penerimaan.baca')
                    <x-ui.navigation-item icon="package-check" :href="route('penerimaan.index')" :active="request()->routeIs('penerimaan.*')">Penerimaan</x-ui.navigation-item>
                @endcan
                @can('penjualan.kelola')
                    <x-ui.navigation-item icon="receipt" :href="route('penjualan.index')" :active="request()->routeIs('penjualan.*')">Penjualan</x-ui.navigation-item>
                @endcan
                @can('retur.baca')
                    <x-ui.navigation-item icon="rotate-left" :href="route('retur.index')" :active="request()->routeIs('retur.*')">Retur</x-ui.navigation-item>
                @endcan
                @can('kartu-stok.baca')
                    <x-ui.navigation-item icon="clipboard-list" :href="route('kartu-stok.index')" :active="request()->routeIs('kartu-stok.*')">Kartu Stok</x-ui.navigation-item>
                @endcan
                @can('stok-barang.baca')
                    <x-ui.navigation-item icon="warehouse" :href="route('stok-barang.index')" :active="request()->routeIs('stok-barang.*')">Stok Barang</x-ui.navigation-item>
                @endcan

            </x-ui.app-sidebar>
        @endauth

        <div class="md3-app-content">
            <x-ui.page-container class="md3-stack" id="konten-utama" tabindex="-1">
                @hasSection('page-header')
                    @yield('page-header')
                @elseif (! request()->routeIs('login'))
                    <x-ui.page-header
                        :icon="match (request()->segment(1)) {
                            'barang' => 'package',
                            'satuan' => 'scale',
                            'vendor' => 'store',
                            'role' => 'shield',
                            'user' => 'person',
                            'margin-penjualan' => 'percent',
                            'pengadaan' => 'shopping-cart',
                            'penerimaan' => 'package-check',
                            'penjualan' => 'receipt',
                            'retur' => 'rotate-left',
                            'kartu-stok' => 'clipboard-list',
                            'stok-barang' => 'warehouse',
                            default => 'home',
                        }"
                        :title="html_entity_decode($__env->yieldContent('heading'), ENT_QUOTES | ENT_HTML5, 'UTF-8')"
                        :subtitle="html_entity_decode($__env->yieldContent('description'), ENT_QUOTES | ENT_HTML5, 'UTF-8')"
                    >
                        @hasSection('page-actions')
                            <x-slot:actions>@yield('page-actions')</x-slot:actions>
                        @endif
                    </x-ui.page-header>
                @endif

                @yield('content')
            </x-ui.page-container>
        </div>
    </div>
    @auth
        <dialog id="mobileNavigation" class="md3-dialog md3-drawer" aria-labelledby="mobileNavigationTitle" data-md3-dialog>
            <div class="md3-drawer__content">
                <header class="md3-drawer__header">
                    <h2 id="mobileNavigationTitle" class="md3-type-title-large">Navigasi utama</h2>
                    <x-ui.icon-button label="Tutup navigasi" icon="close" data-md3-dialog-close />
                </header>
                <div data-md3-drawer-content></div>
            </div>
        </dialog>
        <x-ui.dialog id="confirmRecordAction" title="Konfirmasi tindakan" aria-describedby="confirmRecordMessage">
            <p id="confirmRecordMessage" data-md3-confirm-message></p>
            <x-slot:actions>
                <x-ui.button variant="text" data-md3-dialog-close autofocus>Batal</x-ui.button>
                <x-ui.button variant="outlined" class="md3-action--error" data-md3-confirm-submit>Konfirmasi</x-ui.button>
            </x-slot:actions>
        </x-ui.dialog>
    @endauth
    @stack('scripts')
</body>
</html>

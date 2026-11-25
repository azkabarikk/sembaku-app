@extends('layouts.app')

@section('title', 'Beranda')
@section('heading', 'Sistem Persediaan SembaKu')
@section('description', 'Kelola pengadaan, transaksi, dan stok barang.')

@section('content')
    @guest
        <x-ui.card variant="outlined" class="md3-form-card">
            <p class="md3-muted mb-0">Masuk dengan akun kerja untuk membuka modul yang tersedia bagi Anda.</p>
            <x-slot:footer>
                <x-ui.button :href="route('login')" icon-name="login">Masuk ke SembaKu</x-ui.button>
            </x-slot:footer>
        </x-ui.card>
    @else
        @php
            $shortcuts = [
                ['stok-barang.baca', 'stok-barang.index', 'warehouse', 'Stok Barang', 'Pantau saldo dan ketersediaan barang.'],
                ['administrasi', 'barang.index', 'package', 'Data Barang', 'Kelola harga, satuan, dan status barang.'],
                ['pengadaan.baca', 'pengadaan.index', 'shopping-cart', 'Pengadaan', 'Lihat pesanan dan sisa barang yang belum diterima.'],
                ['penerimaan.baca', 'penerimaan.index', 'package-check', 'Penerimaan', 'Telusuri barang yang diterima dari vendor.'],
                ['penjualan.kelola', 'penjualan.index', 'receipt', 'Penjualan', 'Buka katalog dan keranjang kasir.'],
                ['retur.baca', 'retur.index', 'rotate-left', 'Retur', 'Lihat riwayat pengembalian barang ke vendor.'],
            ];
        @endphp

        <nav class="md3-grid" aria-label="Akses cepat">
            @foreach ($shortcuts as [$ability, $route, $icon, $title, $description])
                @can($ability)
                    <a class="md3-action md3-quick-link" href="{{ route($route) }}">
                        <span class="md3-quick-link__icon"><x-ui.icon :name="$icon" /></span>
                        <span class="md3-quick-link__copy">
                            <strong class="md3-type-title-medium">{{ $title }}</strong>
                            <span class="md3-muted">{{ $description }}</span>
                        </span>
                        <x-ui.icon name="chevron-right" class="md3-quick-link__arrow" />
                    </a>
                @endcan
            @endforeach
        </nav>
    @endguest
@endsection

@extends('layouts.app')

@section('title', 'Beranda')
@section('heading', 'Sistem Persediaan SembaKu')

@section('content')
    <div class="md3-cluster">
        @guest
            <x-ui.button :href="route('login')">Masuk ke SembaKu</x-ui.button>
        @else
            @can('stok-barang.baca')
                <x-ui.button :href="route('stok-barang.index')">Lihat Stok Barang</x-ui.button>
            @endcan
            @can('administrasi')
                <x-ui.button variant="outlined" :href="route('barang.index')">Lihat Data Barang</x-ui.button>
            @endcan
            @can('pengadaan.baca')
                <x-ui.button variant="outlined" :href="route('pengadaan.index')">Pengadaan</x-ui.button>
            @endcan
            @can('penerimaan.baca')
                <x-ui.button variant="outlined" :href="route('penerimaan.index')">Penerimaan</x-ui.button>
            @endcan
        @endguest
    </div>
@endsection

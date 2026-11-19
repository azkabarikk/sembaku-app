<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Komponen SembaKu</title>
    @if (isset($showcaseCss))
        <style>{!! $showcaseCss !!}</style>
    @else
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @endif
    <style>
        .md3-catalog { margin: 0; }
        .md3-catalog *, .md3-catalog *::before, .md3-catalog *::after { box-sizing: border-box; }
        .md3-catalog .md3-page-container { gap: var(--md3-space-40); }
        .md3-catalog .md3-sidebar__brand { color: var(--md3-on-primary-container); }
        .md3-catalog__skip { position: absolute; z-index: 1; inset-inline-start: var(--md3-space-16); }
        .md3-catalog__skip:not(:focus) { clip-path: inset(50%); }
        .md3-catalog__examples { margin: 0; }
        .md3-catalog__swatch { display: grid; min-block-size: var(--md3-space-64); align-content: end; padding: var(--md3-space-12); border-radius: var(--md3-shape-md); }
        .md3-catalog__card-label { display: block; margin: 0; }
        .md3-catalog__card-label + span { display: block; margin-block-start: var(--md3-space-4); }
    </style>
</head>
<body class="md3 md3-catalog">
    <x-ui.button href="#konten" class="md3-catalog__skip">Lewati navigasi</x-ui.button>
    <div class="md3-app-frame">
        <x-ui.app-sidebar id="katalog-nav" label="Navigasi katalog">
            <x-slot:brand><span class="md3-type-title-large">SembaKu</span></x-slot:brand>
            <x-ui.navigation-item href="#fondasi" icon="package" :active="true">Fondasi</x-ui.navigation-item>
            <x-ui.navigation-item href="#aksi">Tombol dan kartu</x-ui.navigation-item>
            <x-ui.navigation-item href="#formulir">Formulir</x-ui.navigation-item>
            <x-ui.navigation-item href="#status">Pesan dan status</x-ui.navigation-item>
            <x-ui.navigation-item href="#data">Tabel dan statistik</x-ui.navigation-item>
            <x-slot:footer>
                <x-ui.divider />
                <x-ui.user-profile username="Akun contoh" role="Supervisor" />
            </x-slot:footer>
        </x-ui.app-sidebar>

        <div>
            <x-ui.app-topbar title="Sistem desain">
                <x-slot:actions>
                    <x-ui.icon-button label="Tentang katalog" icon="info" data-md3-dialog-open="katalog-dialog" />
                </x-slot:actions>
            </x-ui.app-topbar>

            <x-ui.page-container id="konten" class="md3-stack" tabindex="-1">
                <x-ui.page-header title="Katalog komponen" subtitle="Material Design 3 · Fresh Green" />

                <section id="fondasi" class="md3-stack" aria-labelledby="fondasi-title">
                    <x-ui.section-header title="Fondasi" id="fondasi-title" />
                    <div class="md3-grid">
                        @foreach (['primary' => 'Primary', 'primary-container' => 'Primary container', 'secondary-container' => 'Secondary container', 'tertiary-container' => 'Tertiary container', 'error-container' => 'Error container', 'surface' => 'Surface'] as $tone => $label)
                            <div class="md3-catalog__swatch md3-type-label-large" style="background: var(--md3-{{ $tone }}); color: var(--md3-on-{{ $tone }})">{{ $label }}</div>
                        @endforeach
                    </div>
                    <div class="md3-grid">
                        @for ($level = 0; $level <= 5; $level++)
                            <x-ui.surface :$level>Tingkat elevasi {{ $level }}</x-ui.surface>
                        @endfor
                    </div>
                </section>

                <section id="aksi" class="md3-stack" aria-labelledby="aksi-title">
                    <x-ui.section-header title="Tombol dan kartu" id="aksi-title" />
                    <x-ui.card variant="outlined" class="md3-stack">
                        <div class="md3-cluster">
                            @foreach (['filled', 'elevated', 'tonal', 'outlined', 'text'] as $variant)
                                <x-ui.button :$variant>{{ ucfirst($variant) }}</x-ui.button>
                            @endforeach
                            <x-ui.icon-button label="Tambah barang" icon="add" variant="tonal" />
                        </div>
                        <div class="md3-cluster">
                            <x-ui.button size="small" variant="outlined">Kecil</x-ui.button>
                            <x-ui.button size="large" variant="tonal">
                                <x-slot:icon><x-ui.icon name="package" /></x-slot:icon>
                                Lihat barang
                            </x-ui.button>
                            <x-ui.button disabled>Nonaktif</x-ui.button>
                            <x-ui.button :loading="true" variant="tonal">Memproses</x-ui.button>
                        </div>
                    </x-ui.card>
                    <div class="md3-grid">
                        @foreach (['filled' => 'Isi tonal', 'elevated' => 'Elevasi', 'outlined' => 'Garis tepi'] as $variant => $label)
                            <x-ui.card :$variant>
                                <span class="md3-catalog__card-label md3-type-title-medium">{{ $label }}</span>
                                <span class="md3-muted">{{ ucfirst($variant) }}</span>
                            </x-ui.card>
                        @endforeach
                    </div>
                </section>

                <section id="formulir" class="md3-stack" aria-labelledby="formulir-title">
                    <x-ui.section-header title="Formulir" id="formulir-title" />
                    <x-ui.form-section title="Detail barang" description="Gunakan Tab untuk melihat indikator fokus pada setiap kontrol.">
                        <div class="md3-grid">
                            <x-ui.text-field id="contoh-nama" name="nama" label="Nama barang" placeholder="Masukkan nama barang" helper="Contoh: Beras Premium" required />
                            <x-ui.text-field name="kode" label="Kode barang" value="BRG-010" disabled />
                            <x-ui.text-field name="harga" label="Harga beli" type="number" value="-500" error="Harga beli harus lebih dari nol." />
                            <x-ui.select name="satuan" label="Satuan" value="kg" :options="['pcs' => 'Buah', 'kg' => 'Kilogram', 'liter' => 'Liter']" />
                        </div>
                        <x-ui.textarea name="catatan" label="Catatan" rows="3" placeholder="Tulis catatan barang" />
                        <div class="md3-grid">
                            <x-ui.checkbox name="tampilkan" value="1" label="Tampilkan di katalog" checked />
                            <fieldset class="md3-form-section">
                                <legend class="md3-type-label-large">Penyimpanan</legend>
                                <x-ui.radio name="lokasi" value="gudang" label="Gudang" checked />
                                <x-ui.radio name="lokasi" value="toko" label="Toko" />
                            </fieldset>
                            <x-ui.switch name="aktif" value="1" label="Barang aktif" checked />
                        </div>
                    </x-ui.form-section>
                    <x-ui.filter-bar>
                        <x-ui.search-field name="q" label="Cari barang" placeholder="Nama atau kode barang" />
                        <x-ui.select name="status" label="Status" :options="['' => 'Semua status', '1' => 'Aktif', '0' => 'Nonaktif']" />
                        <x-slot:actions><x-ui.button variant="tonal">Terapkan</x-ui.button></x-slot:actions>
                    </x-ui.filter-bar>
                </section>

                <section id="status" class="md3-stack" aria-labelledby="status-title">
                    <x-ui.section-header title="Pesan dan status" id="status-title" />
                    <div class="md3-cluster">
                        @foreach (['success' => 'Aktif', 'warning' => 'Stok menipis', 'error' => 'Habis', 'info' => 'Dalam pengiriman', 'neutral' => 'Draf'] as $tone => $label)
                            <x-ui.chip :variant="$tone">{{ $label }}</x-ui.chip>
                        @endforeach
                        <x-ui.badge variant="info">3 baru</x-ui.badge>
                        <x-ui.chip variant="neutral" :interactive="true" :selected="true">Dipilih</x-ui.chip>
                    </div>
                    <x-ui.alert variant="warning" title="Stok menipis">
                        <x-slot:icon><x-ui.icon name="warning" /></x-slot:icon>
                        Minyak goreng tersisa 4 liter.
                    </x-ui.alert>
                    <x-ui.progress :value="68" variant="info" label="Proses impor 68 persen" />
                    <div class="md3-cluster">
                        <x-ui.button variant="tonal" data-md3-dialog-open="katalog-dialog">Buka dialog</x-ui.button>
                        <x-ui.button variant="outlined" data-md3-snackbar-show="katalog-snackbar">Tampilkan snackbar</x-ui.button>
                    </div>
                    <x-ui.snackbar id="katalog-snackbar" variant="success" hidden>Contoh pesan berhasil.</x-ui.snackbar>
                </section>

                <section id="data" class="md3-stack" aria-labelledby="data-title">
                    <x-ui.section-header title="Tabel dan statistik" id="data-title" />
                    <div class="md3-grid">
                        <x-ui.stat-card title="Total barang" value="120" icon="package" />
                        <x-ui.stat-card title="Stok menipis" value="4" icon="warning" variant="outlined" />
                    </div>
                    <x-ui.table label="Daftar barang contoh">
                        <x-slot:head>
                            <tr><th scope="col">Kode</th><th scope="col">Nama barang</th><th scope="col">Satuan</th><th scope="col">Harga beli</th><th scope="col">Status</th></tr>
                        </x-slot:head>
                        <tr><td>BRG-001</td><td>Beras Premium</td><td>Kilogram</td><td>Rp17.000</td><td><x-ui.chip variant="success">Aktif</x-ui.chip></td></tr>
                        <tr><td>BRG-002</td><td>Minyak Goreng</td><td>Liter</td><td>Rp18.000</td><td><x-ui.chip variant="warning">Menipis</x-ui.chip></td></tr>
                    </x-ui.table>
                    <x-ui.pagination>
                        <nav aria-label="Halaman daftar barang">
                            <ul class="pagination">
                                <li class="page-item active"><span class="page-link" aria-current="page">1</span></li>
                                <li class="page-item"><a class="page-link" href="#data">2</a></li>
                            </ul>
                        </nav>
                    </x-ui.pagination>
                    <x-ui.empty-state title="Belum ada barang" description="Data barang akan tampil setelah ditambahkan." icon="package" />
                </section>
            </x-ui.page-container>
        </div>
    </div>

    <x-ui.dialog id="katalog-dialog" title="Contoh dialog" description="Tekan Escape atau pilih Tutup untuk kembali ke katalog." />
    @if (isset($showcaseJs))
        <script>{!! $showcaseJs !!}</script>
    @endif
</body>
</html>

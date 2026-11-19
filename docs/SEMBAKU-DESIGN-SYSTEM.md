# Sistem desain SembaKu

Fresh Green memakai peran warna semantik, skala tipografi, bentuk, elevasi tonal, dan state interaksi dari Material Design 3. Tampilkan tindakan utama dengan tombol `filled`, gunakan judul sesuai hierarki halaman, dan sertakan teks untuk status agar maknanya tetap jelas tanpa warna. [Material Design 3](https://m3.material.io/)

## Integrasi

SembaKu memakai Blade dan Bootstrap yang dimuat melalui Vite. Entry `resources/js/app.js` mengimpor CSS dan interaksi presentasional dari sistem desain. Tambahkan kelas `.md3` pada pembungkus halaman yang mulai mengadopsi sistem desain untuk mendapatkan latar dan font dasar. Komponen memakai kelas berawalan `md3-`, sehingga dapat diperkenalkan satu per satu dalam layout yang sudah ada.

| File | Tanggung jawab |
| --- | --- |
| `resources/css/md3/tokens.css` | Warna, tipe, ukuran, elevasi, dan motion |
| `resources/css/md3/typography.css` | Kelas `.md3-type-*` |
| `resources/css/md3/foundations.css` | Pembungkus opsional, warna status, fokus, dan state tombol |
| `resources/css/md3/components.css` | Styling komponen Blade |
| `resources/css/md3/utilities.css` | Grid, stack, cluster, elevasi, dan reduced motion |
| `resources/css/md3/index.css` | Urutan impor CSS |
| `resources/js/md3.js` | Dialog, snackbar, navigasi mobile, dan pemulihan pilihan select dengan slot |
| `resources/views/components/ui/showcase.blade.php` | Katalog komponen dengan data contoh |

## Warna

Nama di tabel adalah sufiks dari `--md3-*`. Pasangkan setiap fill dengan teks atau ikon dari kolom `On`. [Peran warna Material 3](https://m3.material.io/styles/color/the-color-system)

| Fill | Warna | On | Warna |
| --- | --- | --- | --- |
| `primary` | `#2E7D32` | `on-primary` | `#FFFFFF` |
| `primary-container` | `#C8F2C2` | `on-primary-container` | `#0E2D10` |
| `secondary` | `#526B50` | `on-secondary` | `#FFFFFF` |
| `secondary-container` | `#DDE9D8` | `on-secondary-container` | `#1A2B19` |
| `tertiary` | `#53633D` | `on-tertiary` | `#FFFFFF` |
| `tertiary-container` | `#E0EBC7` | `on-tertiary-container` | `#1C2510` |
| `error` | `#BA1A1A` | `on-error` | `#FFFFFF` |
| `error-container` | `#FFDAD6` | `on-error-container` | `#410002` |
| `background` | `#F7FAF5` | `on-background` | `#1A1C19` |
| `surface` | `#FCFDF8` | `on-surface` | `#1A1C19` |
| `surface-variant` | `#E2E8DE` | `on-surface-variant` | `#434B43` |
| `inverse-surface` | `#2F332E` | `inverse-on-surface` | `#F0F1EA` |

`outline` (`#73796F`) digunakan untuk batas kontrol. `outline-variant` (`#C3C9BF`) untuk pembatas dekoratif. `inverse-primary` (`#A0D39A`) dipakai untuk aksen dan fokus pada permukaan gelap.

| Surface role | Warna |
| --- | --- |
| `surface-dim` | `#D9DED5` |
| `surface-bright` | `#FCFDF8` |
| `surface-container-lowest` | `#FFFFFF` |
| `surface-container-low` | `#F2F5EE` |
| `surface-container` | `#ECEFE8` |
| `surface-container-high` | `#E6EBE2` |
| `surface-container-highest` | `#E0E5DC` |

Komponen status memakai `--md3-{status}-container` dan `--md3-on-{status}-container`.

| Status | Contoh | Fill / teks |
| --- | --- | --- |
| `success` | Aktif, berhasil | `#D6F3DC` / `#15462B` |
| `warning` | Stok menipis | `#FFECC2` / `#583900` |
| `error` | Gagal, stok habis | `#FFDAD6` / `#410002` |
| `info` | Dalam pengiriman | `#DCEBFF` / `#102F55` |
| `neutral` | Draf | `surface-container-high` / `on-surface-variant` |

## Tipografi

`--md3-font` memilih Roboto jika tersedia, kemudian system sans-serif. Kelas `.md3-type-{peran}` menerapkan ukuran, line height, weight, dan tracking. Nilai di bawah dalam px pada ukuran root standar 16px. Token CSS menggunakan `rem` agar mengikuti ukuran teks browser.

| Peran | Ukuran | Line height | Weight | Tracking |
| --- | ---: | ---: | ---: | ---: |
| `display-large` | 57 | 64 | 400 | -0.25 |
| `display-medium` | 45 | 52 | 400 | 0 |
| `display-small` | 36 | 44 | 400 | 0 |
| `headline-large` | 32 | 40 | 400 | 0 |
| `headline-medium` | 28 | 36 | 400 | 0 |
| `headline-small` | 24 | 32 | 400 | 0 |
| `title-large` | 22 | 28 | 400 | 0 |
| `title-medium` | 16 | 24 | 500 | 0.15 |
| `title-small` | 14 | 20 | 500 | 0.10 |
| `body-large` | 16 | 24 | 400 | 0.50 |
| `body-medium` | 14 | 20 | 400 | 0.25 |
| `body-small` | 12 | 16 | 400 | 0.40 |
| `label-large` | 14 | 20 | 500 | 0.10 |
| `label-medium` | 12 | 16 | 500 | 0.50 |
| `label-small` | 11 | 16 | 500 | 0.50 |

## Bentuk, jarak, dan elevasi

`--md3-shape-*` menyediakan `none` (0), `xs` (4px), `sm` (8px), `md` (12px), `lg` (16px), `xl` (28px), dan `full` (999px). Field dan chip memakai `sm`, kartu `md`, dialog `xl`, dan tombol `full`.

`--md3-space-{n}` menyediakan 4, 8, 12, 16, 20, 24, 32, 40, 48, 56, dan 64px, dengan 0 untuk reset. Gunakan `.md3-stack` untuk alur vertikal, `.md3-cluster` untuk kelompok horizontal yang bisa pindah baris, dan `.md3-grid` untuk kolom adaptif.

| Level | `--md3-tone-{n}` | Penggunaan |
| --- | --- | --- |
| 0 | `surface` | Permukaan dasar |
| 1 | `surface-container-low` | Kartu elevated |
| 2 | `surface-container` | Permukaan di atas kartu |
| 3 | `surface-container-high` | Dialog |
| 4 | `surface-container-highest` | Lapisan atas |
| 5 | `surface-dim` | Lapisan dengan pembeda tonal paling kuat |

Gunakan `x-ui.surface` atau `.md3-elevation-{n}`. Token `--md3-shadow-{n}` mengikuti level yang sama dan mendukung pemisahan tonal.

## Motion dan aksesibilitas

- Hover, fokus, dan pressed menggunakan lapisan warna 8%, 10%, dan 12%. Fokus keyboard menampilkan outline yang terlihat.
- `aria-current`, `aria-pressed`, dan `aria-expanded` menyatakan halaman aktif, pilihan aktif, dan keadaan drawer. Atribut `disabled` dan `checked` tetap berasal dari elemen HTML native.
- Transisi standar memakai 160ms, perubahan cepat 100ms, masuk 220ms, dan keluar 140ms. `prefers-reduced-motion` menonaktifkan animasi bergerak dan waktu transisi.
- Gunakan label yang terlihat untuk input dan nama aksesibel untuk tombol ikon. Teks bantuan serta error ditautkan dengan `aria-describedby`. Uji kontras teks normal ke latar dengan target 4,5:1 dan batas kontrol atau indikator fokus dengan target 3:1. [WCAG 2.2](https://www.w3.org/TR/WCAG22/)

## API Blade

Semua nama di bawah dipanggil dengan prefiks `<x-ui.…>`. Atribut tambahan diteruskan ke elemen komponen. Untuk field, atribut diterapkan pada kontrol native. Gunakan `:prop="..."` untuk nilai PHP termasuk boolean dan array.

| Komponen | Props dan penggunaan | Slot |
| --- | --- | --- |
| `button` | `variant="filled"` (`filled`, `elevated`, `tonal`, `outlined`, `text`), `size="medium"` (`small`, `medium`, `large`), `type="submit"`, `disabled`, `loading`, `full-width`, atau `href` untuk tautan | Teks, `icon` |
| `icon-button` | `label="Tambah barang"`, `icon="add"`, `variant="tonal"`. Menerima props `button` | Ikon kustom jika `icon` kosong |
| `icon` | `name="package"`. Ikon garis bulat dekoratif. Tersedia `add`, `close`, `search`, `menu`, `person`, `package`, `check`, `arrow-right`, `warning`, `info` | — |
| `card` | `variant="elevated"` (`filled`, `elevated`, `outlined`) | Konten, `header`, `footer` |
| `divider` | `orientation="horizontal"` atau `vertical` | — |
| `surface` | `:level="2"` (0 sampai 5) | Konten |
| `text-field` | `name="nama"`, `label="Nama barang"`, `type="text"`, `value`, `placeholder`, `readonly` | — |
| `textarea` | `name="catatan"`, `label="Catatan"`, `value`, `placeholder`, `readonly`, `rows="4"` | — |
| `select` | `name="idsatuan"`, `label="Satuan"`, `value`, `:options="[1 => 'Kilogram']"`, `multiple` | `<option>` atau `<optgroup>` kustom |
| `checkbox` | `name="aktif"`, `label="Aktif"`, `value="1"`, `checked` | — |
| `radio` | `name="lokasi"`, `label="Gudang"`, `value="gudang"`, `checked`. Kelompokkan dengan `name` yang sama | — |
| `switch` | `name="tampilkan"`, `label="Tampilkan"`, `value="1"`, `checked`. Nilai dikirim sebagai checkbox native | — |
| `badge` | `variant="info"` | Teks status atau jumlah |
| `chip` | `variant="success"`, `interactive`, `:selected="true"`, `disabled`, `type`. Pemanggil menentukan state dan perilaku chip interaktif | Teks, `icon` |
| `alert` | `variant="warning"`, `title="Stok menipis"` | Pesan, `icon` |
| `snackbar` | `id`, `variant="neutral"`, `:dismissible="false"`, `hidden`. Pembuka memakai `data-md3-snackbar-show="id"` | Pesan, `actions` |
| `progress` | `:value="68"` (0–100, `null` berarti tidak pasti), `label="Proses impor"`, `variant="info"` | — |
| `table` | `label="Daftar barang"`, `caption`. Header kolom memakai `<th scope="col">` | Baris `<tr>`, `head`, `foot` |
| `stat-card` | `title="Total barang"`, `value="120"`, `icon="package"`, `variant` seperti `card` | Keterangan opsional |
| `empty-state` | `title="Belum ada barang"`, `description`, `icon` | Aksi opsional |
| `pagination` | `label="Paginasi barang"`. Membungkus hasil `links()` Bootstrap atau markup daftar dengan `.md3-pagination__list` dan `.md3-pagination__link` | Paginasi |
| `page-header` | `title="Master Barang"`, `subtitle`, `heading="h1"` (`h1` sampai `h3`) | Konten, `actions` |
| `section-header` | API `page-header`, default `heading="h2"` | Konten, `actions` |
| `form-section` | `title`, `description`. Mengelompokkan field melalui `<fieldset>` dan `<legend>` | Field |
| `search-field` | `label="Cari"` dan props `text-field` dengan tipe `search` | — |
| `filter-bar` | Masukkan ke form pencarian yang sudah ada | Field, `actions` |
| `dialog` | `id`, `title`, `description`. Tombol pembuka memakai `data-md3-dialog-open="id"` | Isi dialog, `actions` dengan `data-md3-dialog-close` pada tombol tutup |
| `app-sidebar` | `id` untuk konten drawer, `label="Navigasi utama"`. Desktop permanen, mobile collapsible | Navigasi, `brand`, `footer` |
| `app-topbar` | `title` | Konten, `leading`, `actions` |
| `navigation-item` | `href`, `:active="true"`, `icon` | Teks, `leading` |
| `user-profile` | `username`, `role` dari layout atau akun yang sedang login | `avatar` opsional |
| `page-container` | Pembungkus `<main>` | Konten halaman |

Props umum field meliputi `id`, `name`, `label`, `value`, `required`, `disabled`, `helper`, dan `error`. `name` menghubungkan input ke `old()` dan error Laravel, termasuk nama seperti `barang[nama]`. Gunakan `:options` agar selected dirender di server. Pada slot opsi kustom, JavaScript menyelaraskan state dengan `old()` atau `value`. Jika halaman harus mendukung JavaScript nonaktif, isi `@selected` secara eksplisit pada opsi kustom. Tipe password dan file mengecualikan pemulihan nilai dari `old()`.

Varian `success`, `warning`, `error`, `info`, dan `neutral` tersedia untuk badge, chip, alert, snackbar, dan progress.

## Contoh

Letakkan komponen di dalam form yang sudah memiliki `action`, `method`, `enctype`, dan proteksi CSRF sesuai kebutuhan modul.

```blade
<x-ui.page-header title="Master Barang" subtitle="Kelola data barang" />

<x-ui.card variant="elevated">
    <x-ui.form-section title="Identitas barang">
        <x-ui.text-field name="nama" id="nama" label="Nama Barang" :value="$barang->nama ?? null" required />
        <x-ui.select name="idsatuan" label="Satuan" :options="$opsiSatuan" :value="$barang->idsatuan ?? null" required />
        <x-ui.button variant="filled" type="submit">Simpan</x-ui.button>
    </x-ui.form-section>
</x-ui.card>
```

```blade
<x-ui.button variant="outlined" data-md3-dialog-open="bantuan">Buka bantuan</x-ui.button>
<x-ui.dialog id="bantuan" title="Bantuan" description="Pilih satuan yang sesuai untuk barang ini." />

@auth
    <x-ui.user-profile :username="auth()->user()->username" :role="auth()->user()->nama_role" />
@endauth
```

Untuk layout baru, susun `app-sidebar` dan kolom konten di dalam `.md3.md3-app-frame`. Letakkan `app-topbar` dan `page-container` di dalam kolom konten. Pertahankan kondisi akses dan `route()` yang disediakan aplikasi pada saat menyusun menu.

## Katalog dan pengembangan komponen

Katalog `components.ui.showcase` dapat dirender sebagai HTML lokal tanpa menambahkan route. Setelah `npm run build`, jalankan perintah berikut dari root proyek. Buka `storage/app/md3-showcase.html` di browser.

```powershell
@'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array']);
$manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
$files = [$manifest['resources/sass/app.scss']['file'], ...$manifest['resources/js/app.js']['css']];
$css = implode("\n", array_map(fn ($file) => file_get_contents(public_path('build/'.$file)), $files));
$js = file_get_contents(resource_path('js/md3.js'));
file_put_contents(storage_path('app/md3-showcase.html'), view('components.ui.showcase', [
    'showcaseCss' => $css,
    'showcaseJs' => $js,
])->render());
'@ | php
```

- Gunakan komponen dasar dan token yang tersedia sebelum menambah varian. Letakkan nilai warna di `tokens.css` dan gunakan peran semantik di stylesheet komponen.
- Lingkupi selector dengan kelas `md3-`. Reuse elemen HTML native agar interaksi dan pengiriman form tetap berjalan.
- Teruskan atribut form dari pemanggil. Berikan slot khusus hanya saat pemanggil perlu memilih markup, misalnya kolom tabel, ikon, atau aksi dialog.
- Periksa komponen di katalog pada desktop dan mobile. Coba navigasi Tab, Enter, dan Escape, label pembaca layar, kontras, serta reduced motion sebelum mengadopsinya pada halaman bisnis.

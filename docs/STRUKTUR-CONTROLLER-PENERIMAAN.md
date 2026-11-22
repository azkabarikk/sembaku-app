# Struktur controller Penerimaan

Catatan sejarah refaktor. Struktur lima controller dan konfirmasi manual di bawah ini sudah digantikan. Untuk kode yang digunakan sekarang, lihat [Pengadaan dan Penerimaan](STRUKTUR-TRANSAKSI-SEMBAKU.md).

Sembilan route bernama `penerimaan.*` di `routes/web.php` menghubungkan aksi pengguna dengan lima controller di `app/Http/Controllers/Transaksi/Penerimaan`. Untuk menjelaskan satu operasi, mulai dari baris di bawah lalu ikuti pemanggilan Query atau Service di dalam method tersebut.

| Route | Controller dan method | Perilaku |
| --- | --- | --- |
| `penerimaan.index` | `ReadController::index` | Menampilkan daftar, filter pengadaan, dan pilihan pengadaan yang masih memiliki sisa. |
| `penerimaan.show` | `ReadController::show` | Membaca header, vendor, petugas, dan rincian barang. ID yang tidak ditemukan menghasilkan 404. |
| `penerimaan.pilih-pengadaan` | `ReadController::pilihPengadaan` | Mengarahkan pilihan ID positif ke formulir. Pilihan tidak valid kembali ke daftar dengan pesan. |
| `penerimaan.create` | `ReadController::create` | Menampilkan formulir untuk pengadaan aktif. Jika barang sudah diterima seluruhnya atau dialokasikan ke draf lain, pengguna kembali ke daftar. |
| `penerimaan.store` | `CreateController::store` | Mengambil ID petugas login, menyimpan draf melalui Service, lalu membuka detailnya. |
| `penerimaan.edit` | `UpdateController::edit` | Membaca header, meminta izin edit, lalu memuat rincian draf dan sisa pesanan per barang. |
| `penerimaan.update` | `UpdateController::update` | Mengirim input tervalidasi ke Service dan mengarahkan pengguna ke detail draf. |
| `penerimaan.destroy` | `DeleteController::destroy` | Meminta Service menghapus draf, lalu kembali ke daftar. |
| `penerimaan.confirm` | `ConfirmController::confirm` | Mengambil ID pengadaan, meminta Service memproses konfirmasi, lalu menampilkan hasil di halaman detail. |

## Alur antar-file

Saat membuka halaman, route memeriksa hak akses dan memanggil controller. Controller membaca data lewat `app/Queries/Transaksi/PenerimaanQuery.php` dan meneruskannya ke Blade. Saat menyimpan atau mengubah draf, Laravel lebih dulu menjalankan `DrafPenerimaanRequest::rules` di `app/Http/Requests`. Jumlah wajib diisi sebagai array bilangan bulat dari 0 sampai 2.147.483.647. Harga boleh kosong dan memakai batas yang sama jika diisi.

Controller lalu memanggil `app/Services/Transaksi/PenerimaanService.php`. Service memeriksa aturan yang bergantung pada keadaan database, seperti sisa pesanan dan status draf. Seluruh operasi tulis berada di dalam transaksi. Laravel menyediakan Query dan Service melalui constructor sesuai kebutuhan masing-masing controller.

Contoh dari `CreateController::store` saat petugas menyimpan formulir.

```php
$idUser = (int) $request->user()->getAuthIdentifier();
$id = $this->service->simpanDraf($idpengadaan, $idUser, $request->validated());

return redirect()->route('penerimaan.show', $id)->with('success', 'Draf penerimaan berhasil disimpan.');
```

## Empat alur utama

1. **Buat draf.** `create` menampilkan pengadaan aktif beserta barang yang masih tersisa. `store` memvalidasi formulir dan mengambil ID petugas dari akun login. `simpanDraf` mengunci pengadaan, memeriksa sisa per barang, menghitung subtotal, lalu menyimpan header berstatus `D` dan minimal satu detail dengan jumlah positif. Harga awal berasal dari snapshot `detail_pengadaan.harga_satuan`. Setelah berhasil, petugas diarahkan ke detail penerimaan.
2. **Ubah draf.** `edit` memeriksa hak pengguna sebelum menampilkan formulir. `perbaruiDraf` mengunci pengadaan lalu penerimaan, memeriksa lagi status dan kepemilikan, serta memastikan pengadaan aktif. Perhitungan sisa mengabaikan draf yang sedang diedit. Jika harga aktual dikosongkan, aplikasi memakai harga detail yang tersimpan, lalu harga pesanan jika detailnya baru. Setelah memeriksa referensi retur, aplikasi mengganti detail dan menghitung ulang subtotal. Identitas pembuat tetap melekat pada header.
3. **Hapus draf.** `hapusDraf` mengunci pengadaan dan penerimaan, lalu memeriksa hak untuk mengubah draf serta referensi di `retur` dan `detail_retur`. Jika lolos, aplikasi menghapus detail dahulu, kemudian header. Status `K` atau adanya referensi retur menghentikan penghapusan.
4. **Konfirmasi.** Route membatasi konfirmasi kepada Admin dan Supervisor. Service mengunci pengadaan dan penerimaan lalu memastikan status penerimaan `D`, pengadaan terkait aktif, dan ID muat di kolom referensi kartu stok. Service mengunci detail dan barang menurut ID barang, lalu memeriksa jumlah, harga, subtotal, duplikasi barang, status aktif, sisa pesanan, saldo stok, dan riwayat mutasi. Setiap detail yang sah menghasilkan satu mutasi masuk `M` dan memperbarui `barang.harga` dengan harga aktual. Langkah terakhir mengubah status penerimaan menjadi `K`.

**Contoh perhitungan.** Pesanan 10 dus dengan snapshot Rp17.000 dapat diterima bertahap. Saat draf berisi 6 dus dengan harga aktual Rp18.000, subtotal draf menjadi Rp108.000. Sebelum konfirmasi, 4 dus tersedia untuk draf berikutnya. Setelah konfirmasi, mutasi masuk berjumlah 6, harga master menjadi Rp18.000, dan riwayat pesanan tetap mencatat Rp17.000.

## Method query

| Method | Data yang diambil |
| --- | --- |
| `daftarPenerimaan` | Daftar berdasarkan ID penerimaan menaik, termasuk status, vendor, petugas, dan filter pengadaan opsional. |
| `headerDetail` | Identitas, tanggal, status, vendor, dan petugas untuk halaman detail. |
| `detailPenerimaan` | Rincian barang dan satuan, jumlah dipesan, jumlah diterima, harga aktual, serta subtotal. |
| `headerUntukEdit` | Header singkat dengan ID petugas dan status yang dibutuhkan untuk otorisasi edit. |
| `idPengadaanPenerimaan` | ID pengadaan induk untuk menentukan baris yang harus dikunci lebih dulu. Menghasilkan 404 jika penerimaan tidak ditemukan. |
| `rincianDraf` | Jumlah dan harga tersimpan, dikelompokkan menurut ID barang untuk pengisian formulir dan perhitungan ulang. |
| `pengadaanTersisa` | Pengadaan aktif yang masih memiliki barang untuk diterima, beserta total sisa untuk pilihan di halaman daftar. |
| `pengadaanAktif` | Header pengadaan aktif dan nama vendor untuk halaman buat. |
| `barisPesanan` | Snapshot harga dan jumlah pesanan tiap barang, jumlah final, jumlah draf, serta sisanya. Dapat mengabaikan draf yang diedit atau menyertakan baris bersisa nol untuk validasi. |
| `jumlahDipesanPerBarang` | Total jumlah tiap barang pada detail pengadaan sebagai batas saat konfirmasi. |
| `jumlahDiterimaPerBarang` | Total penerimaan berstatus `K` dan status aktif lama `A` per barang untuk pemeriksaan saat konfirmasi. |

Daftar dan formulir menghitung sisa per barang dengan `maksimum(0, dipesan − diterima − draf lain)`. Ketika mengonfirmasi, Service membandingkan jumlah aktual dengan `dipesan − diterima final`. Status lama `N` (dibatalkan) dikecualikan dari kedua perhitungan. Data nama dan satuan dibaca melalui `v_master_barang`, nama petugas melalui `v_master_user`, dan vendor melalui `v_master_vendor`.

## Method service

| Method | Tugas |
| --- | --- |
| `__construct` | Menerima Query untuk membaca data yang dipakai dalam transaksi. |
| `simpanDraf` | Menjalankan transaksi pembuatan header serta detail dan mengembalikan ID penerimaan baru. |
| `perbaruiDraf` | Memeriksa hak dan kondisi pengadaan, memeriksa retur, lalu mengganti detail secara atomik. |
| `hapusDraf` | Memeriksa hak dan retur, lalu menghapus detail dan header dalam satu transaksi. |
| `konfirmasi` | Mengatur urutan penguncian, pemeriksaan, pencatatan stok dan harga, lalu perubahan status menjadi `K`. |
| `kunciPengadaan` | Mengunci header pengadaan dengan `FOR UPDATE` dan mengambil status terkininya. |
| `otorisasiDrafTerkunci` | Mengunci penerimaan dan menjalankan gate pada status serta ID pembuat yang baru dibaca. Admin dan Supervisor dapat mengelola seluruh draf. Petugas Gudang hanya dapat mengelola draf miliknya. |
| `rincianUntukSimpan` | Memastikan barang berasal dari pesanan dan jumlah terima tidak melebihi sisa. Melewati baris berjumlah nol, memilih harga aktual, dan menghitung subtotal. Minimal satu barang harus memiliki jumlah positif dan setiap subtotal harus muat di kolom database. |
| `simpanDetail` | Menyimpan satu baris barang, jumlah, harga, dan subtotal draf dengan parameter binding. |
| `pastikanBelumDiretur` | Memeriksa referensi retur pada header dan detail dengan `FOR SHARE` sebelum detail diganti atau dihapus. |
| `kunciDanPeriksaBarang` | Memastikan ID barang unik, jumlah positif, harga dan subtotal cocok, lalu mengunci barang dan memeriksa status aktifnya. |
| `validasiKonfirmasi` | Menolak mutasi masuk yang sudah ada, termasuk kode lama `P`. Memeriksa sisa pesanan final, saldo negatif, dan penambahan stok yang melampaui batas bilangan bulat. |
| `catatStokDanHarga` | Menulis mutasi masuk per detail dan memperbarui harga pembelian terakhir di master barang. |
| `syaratKonfirmasi` | Menghentikan konfirmasi dengan pesan validasi jika syarat yang diberikan gagal. |

## Membaca transaksi dan Raw SQL

`DB::transaction(..., 3)` membungkus masing-masing operasi tulis. Semua perubahan disahkan bersama saat transaksi berhasil. Jika terjadi exception, Laravel melakukan rollback. Angka 3 memberi Laravel hingga tiga percobaan untuk kegagalan konkurensi yang dapat dicoba ulang.

`SELECT ... FOR UPDATE` menahan kunci pada baris yang dibaca selama transaksi. Ketika dua petugas memproses pengadaan yang sama, permintaan berikutnya menunggu kunci pada pengadaan tersebut. Saat konfirmasi, detail diurutkan berdasarkan ID barang sehingga transaksi yang menyentuh beberapa barang mengambil kunci dalam urutan yang konsisten. Saldo dibaca setelah semua barang terkait dikunci.

Berikut urutan penulisan untuk satu barang saat konfirmasi. Setiap tanda `?` diisi melalui parameter binding dari data transaksi yang telah diperiksa.

```sql
INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang)
VALUES (?, ?, 0, ?, ?);

UPDATE barang SET harga = ? WHERE idbarang = ?;
```

Binding pertama berisi kode `M`, jumlah aktual, ID penerimaan, lalu ID barang. Trigger `trg_validasi_kartu_stok` mengisi saldo `stock` saat INSERT. Binding kedua berisi harga aktual dan ID barang. Setelah semua detail selesai, Service menjalankan `UPDATE penerimaan SET status = ? WHERE idpenerimaan = ?` dengan nilai `K` dan ID penerimaan. Jika pembaruan harga barang kedua gagal, misalnya, mutasi barang pertama ikut dibatalkan dan penerimaan tetap draf.

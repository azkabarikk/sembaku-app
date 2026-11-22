# Alur penerimaan barang di SembaKu

Dokumen ini mencatat tahap pertama refaktor dengan konfirmasi manual. Untuk struktur dan alur penyimpanan yang dipakai sekarang, lihat [Pengadaan dan Penerimaan](STRUKTUR-TRANSAKSI-SEMBAKU.md).

Saat petugas menyimpan 6 dus dari pesanan 10 dus, aplikasi menyimpan draf dan menyisakan 4 dus untuk draf lain. Stok baru bertambah setelah Admin atau Supervisor mengonfirmasi penerimaan. Contoh ini bisa dipakai untuk menelusuri ketiga file saat presentasi.

## Hubungan ketiga file

| File di `app/Http/Controllers/Transaksi` | Kapan dibaca |
| --- | --- |
| `PenerimaanController.php` | Mulai di sini untuk melihat apa yang terjadi ketika pengguna membuka halaman atau mengirim formulir. |
| `Queries/PenerimaanQuery.php` | Buka untuk melihat asal data daftar, detail, harga pesanan, jumlah draf, dan sisa per barang. |
| `Services/PenerimaanService.php` | Buka untuk mengikuti aturan transaksi sejak pengadaan dikunci sampai draf disimpan, diubah, dihapus, atau dikonfirmasi. |

Laravel memberikan Query dan Service melalui constructor controller. Saat menampilkan halaman, controller membaca data lewat Query lalu mengirimnya ke Blade. Saat menyimpan perubahan, controller memanggil Service. Service menggunakan Query untuk pembacaan yang dibutuhkan dan menjalankan SQL penguncian serta penulisan di dalam transaksi.

## Empat alur utama

1. **Buat draf.** `create` menampilkan pengadaan aktif beserta barang yang masih tersisa. `store` memvalidasi formulir dan mengambil ID petugas dari akun login. `simpanDraf` mengunci pengadaan, memeriksa sisa per barang, menghitung subtotal, lalu menyimpan header berstatus `D` dan minimal satu detail dengan jumlah positif. Harga awal berasal dari snapshot `detail_pengadaan.harga_satuan`. Setelah berhasil, petugas diarahkan ke detail penerimaan.
2. **Ubah draf.** `edit` memeriksa hak pengguna sebelum menampilkan formulir. `perbaruiDraf` mengunci pengadaan lalu penerimaan, memeriksa lagi status dan kepemilikan, serta memastikan pengadaan aktif. Perhitungan sisa mengabaikan draf yang sedang diedit. Jika harga aktual dikosongkan, aplikasi memakai harga detail yang tersimpan, lalu harga pesanan jika detailnya baru. Setelah memeriksa referensi retur, aplikasi mengganti detail dan menghitung ulang subtotal. Identitas pembuat tetap melekat pada header.
3. **Hapus draf.** `hapusDraf` mengunci pengadaan dan penerimaan, lalu memeriksa hak untuk mengubah draf serta referensi di `retur` dan `detail_retur`. Jika lolos, aplikasi menghapus detail dahulu, kemudian header. Status `K` atau adanya referensi retur menghentikan penghapusan.
4. **Konfirmasi.** Route membatasi konfirmasi kepada Admin dan Supervisor. Service mengunci pengadaan dan penerimaan lalu memastikan status penerimaan `D`, pengadaan terkait aktif, dan ID muat di kolom referensi kartu stok. Service mengunci detail dan barang menurut ID barang, lalu memeriksa jumlah, harga, subtotal, duplikasi barang, status aktif, sisa pesanan, saldo stok, dan riwayat mutasi. Setiap detail yang sah menghasilkan satu mutasi masuk `M` dan memperbarui `barang.harga` dengan harga aktual. Langkah terakhir mengubah status penerimaan menjadi `K`.

**Contoh perhitungan.** Pesanan 10 dus dengan snapshot Rp17.000 dapat diterima bertahap. Saat draf berisi 6 dus dengan harga aktual Rp18.000, subtotal draf menjadi Rp108.000. Sebelum konfirmasi, 4 dus tersedia untuk draf berikutnya. Setelah konfirmasi, mutasi masuk berjumlah 6, harga master menjadi Rp18.000, dan riwayat pesanan tetap mencatat Rp17.000.

## Method controller

| Method | Tugas |
| --- | --- |
| `__construct` | Menerima Query dan Service dari Laravel. |
| `index` | Membaca filter pengadaan dari URL. ID positif menyaring daftar, sedangkan nilai kosong atau tidak valid menampilkan seluruh penerimaan. Menyiapkan pilihan pengadaan yang tersisa. |
| `pilihPengadaan` | Mengarahkan ID yang valid ke halaman buat. Pilihan tidak valid mengembalikan pengguna ke daftar dengan pesan. |
| `create` | Membuka formulir untuk pengadaan aktif. Menghasilkan 404 jika pengadaan tidak ditemukan atau tidak aktif, dan kembali ke daftar jika sisanya habis. |
| `store` | Memvalidasi input, mengambil ID petugas login, memanggil Service, dan mengarahkan ke detail draf baru. |
| `show` | Mengambil header dan rincian barang untuk halaman detail. Header yang tidak ditemukan menghasilkan 404. |
| `edit` | Memeriksa gate `penerimaan.ubah-draf` lalu memuat data formulir milik draf tersebut. |
| `update` | Memvalidasi formulir, meminta Service memperbarui detail, lalu kembali ke halaman detail. |
| `destroy` | Meminta Service menghapus draf lalu kembali ke daftar. |
| `confirm` | Mencari pengadaan terkait dan memanggil Service. Menampilkan pesan validasi atau keberhasilan pada halaman detail. Kesalahan SQL saat transaksi dilaporkan ke log dan ditampilkan sebagai pesan umum. |
| `validasiPenerimaan` | Memeriksa array jumlah dan harga. Nilai numerik yang diisi harus bilangan bulat dari 0 hingga 2.147.483.647. Harga boleh kosong. Service memeriksa aturan lintas barang dan pesanan. |

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

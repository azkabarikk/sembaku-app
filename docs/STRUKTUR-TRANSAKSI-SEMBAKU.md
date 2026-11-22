# Pengadaan dan Penerimaan di SembaKu

## Mulai dari aksi pengguna

| File | Tanggung jawab |
| --- | --- |
| `app/Http/Controllers/Transaksi/Pengadaan/PengadaanController.php` | `index` dan `show` menampilkan pesanan. `create` dan `edit` memuat formulir. `store`, `update`, dan `destroy` mengarahkan permintaan ke Service dan mengembalikan respons. |
| `app/Services/Transaksi/PengadaanService.php` | `simpan`, `perbarui`, dan `hapus` menangani header dan detail dalam satu transaksi. Service memeriksa vendor dan barang, mengambil harga pesanan, serta menghitung subtotal, PPN, dan total. |
| `app/Queries/Transaksi/PengadaanQuery.php` | Membaca vendor, barang, header, rincian, dan progres penerimaan. Rincian menyandingkan jumlah dipesan dengan jumlah diterima per barang. |
| `app/Http/Controllers/Transaksi/Penerimaan/PenerimaanController.php` | Menangani daftar, detail, pilihan pengadaan, formulir dan penyimpanan penerimaan, serta pengelolaan draf. |
| `app/Services/Transaksi/PenerimaanService.php` | Memvalidasi jumlah, harga, sisa pesanan, dan kepemilikan draf. Saat membukukan penerimaan, Service memeriksa status dan barang, lalu mencatat stok serta memperbarui harga pembelian terakhir. |
| `app/Queries/Transaksi/PenerimaanQuery.php` | Membaca pengadaan yang tersedia, harga snapshot, penerimaan sebelumnya, sisa barang, petugas, vendor, serta data form edit dan halaman detail. |

Route memeriksa izin akun login sebelum meneruskan permintaan ke controller. Untuk menampilkan halaman, controller mengambil data dari Query lalu mengirimnya ke Blade. Untuk mengubah data, controller memanggil Service. Form Request Penerimaan memvalidasi format jumlah dan harga, sedangkan Service memeriksa nilainya terhadap pesanan di database.

## CRUD Pengadaan

1. **Tambah.** Petugas memilih vendor, barang, dan jumlah. Service mengambil harga dari master barang, menghitung subtotal tiap baris, menjumlahkannya, lalu menghitung PPN 11% di PHP dengan pembulatan rupiah. Header dan detail disimpan bersama. Contohnya, 5 dus × Rp12.000 menghasilkan subtotal Rp60.000, PPN Rp6.600, dan total Rp66.600.
2. **Baca.** Daftar menampilkan nilai pesanan dan progres. Halaman detail menampilkan barang, harga saat dipesan, jumlah diterima, dan sisa. Penerimaan final berstatus `K` dan riwayat aktif lama `A` dihitung dalam progres.
3. **Ubah.** Service mengunci pengadaan dan memeriksa apakah sudah ada penerimaan. Jika masih boleh diedit, barang lama menggunakan harga dari detail pesanan dan barang tambahan menggunakan harga master saat ditambahkan. Total dan PPN dihitung ulang.
4. **Hapus.** Pengadaan yang memiliki penerimaan dilindungi. Jika pesanan dapat dihapus, Service menghapus detail lalu header dalam transaksi yang sama.

## Penerimaan langsung menambah stok

1. Admin, Supervisor, atau Petugas Gudang memilih pengadaan aktif. Form menampilkan sisa per barang setelah mengurangi penerimaan final (K dan A lama) dan jumlah yang dialokasikan pada draf lain (D). Riwayat batal lama (N) tidak memakai jatah pesanan.
2. Saat **Simpan Penerimaan** ditekan, controller mengambil identitas petugas dari login. Service mengunci pengadaan, lalu barang yang diterima menurut urutan ID. Form membawa ID penerimaan terakhir yang terlihat saat dibuka. Jika ada penerimaan baru untuk pesanan yang sama, petugas diminta memeriksa sisa sebelum mengirim lagi.
3. Service memeriksa asal dan status barang, sisa pesanan, jumlah, harga, subtotal, dan kapasitas saldo. Harga yang dikosongkan mengambil snapshot harga pesanan. Setidaknya satu barang harus diterima.
4. Dalam satu transaksi Laravel, Service menyimpan header sementara D, menyimpan detail, mencatat mutasi M per barang, memperbarui harga beli terakhir, lalu menetapkan status K. Trigger mengisi saldo kartu stok. Halaman detail menampilkan pesan berhasil setelah commit. Jika satu penulisan gagal, header, detail, mutasi, dan perubahan harga ikut dibatalkan.

Contoh SQL untuk penerimaan 3 dus dengan harga aktual Rp13.000:

```sql
INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang)
VALUES (?, ?, 0, ?, ?);
UPDATE barang SET harga = ? WHERE idbarang = ?;
```

Binding untuk INSERT berisi kode M, jumlah 3, ID penerimaan, dan ID barang. Binding untuk UPDATE berisi harga aktual 13000 dan ID barang. Subtotal detail penerimaan adalah Rp39.000.

**Draf lama.** Pembuat yang berhak, Admin, atau Supervisor dapat mengedit dan menyimpan draf lama untuk langsung membukukannya. ID penerimaan dan identitas pembuat dipertahankan. Draf yang belum dibukukan dapat dihapus sesuai hak akses. Referensi retur dan mutasi masuk yang sudah ada melindungi riwayat. Penerimaan yang sudah berstatus K terkunci. Koreksi sesudah pembukuan memerlukan proses retur atau koreksi tersendiri.

## Harga dan penguncian

`detail_pengadaan.harga_satuan` menyimpan harga ketika barang dipesan. `detail_penerimaan.harga_satuan_terima` menyimpan harga aktual ketika barang diterima. Saat stok dibukukan, `barang.harga` menjadi harga pembelian terakhir. Misalnya, pesanan Rp12.000 yang diterima pada harga Rp13.000 tetap tercatat Rp12.000 dalam riwayat pengadaan. Fungsi `fn_harga_jual` menggunakan harga dasar dan margin untuk menghitung harga jual.

Contoh SQL pada Service Pengadaan:

```sql
SELECT idpengadaan, vendor_idvendor
FROM pengadaan
WHERE idpengadaan = ? FOR UPDATE
```

Aplikasi mengirim ID sebagai parameter binding. `FOR UPDATE` mengunci baris pengadaan sampai transaksi selesai, sehingga pembaruan atau penghapusan dapat memeriksa kembali keberadaan penerimaan saat memegang kunci. `DB::transaction()` menyimpan seluruh operasi bersama dan melakukan rollback jika salah satu operasi gagal.

## Batas transaksi pada database yang diperiksa

Pada database operasional, `sp_konfirmasi_penerimaan` membuka transaksi sendiri, mengunci pengadaan dan penerimaan, memeriksa status D dan jumlah detail, mengubah status menjadi K, menulis mutasi masuk M, lalu melakukan commit. Prosedur tersebut tidak menulis harga master. Trigger `trg_validasi_kartu_stok` memeriksa arah mutasi, mengunci barang, membaca saldo, menolak saldo negatif, dan mengisi kolom stock.

Memanggil prosedur tersebut di tengah transaksi Laravel akan mengakhiri transaksi luar saat MySQL menjalankan `START TRANSACTION`. Memanggilnya setelah menyimpan draf juga akan memisahkan penyimpanan header/detail, pembukuan stok, dan pembaruan harga. Jika langkah terakhir gagal, rollback dari Laravel tidak dapat membatalkan stok yang sudah di-commit oleh prosedur.

Sesuai keputusan proyek, Service menggunakan Raw SQL Laravel sebagai satu-satunya penulis mutasi pada alur ini. Prosedur tersimpan tetap tersedia di MySQL dan tidak dipanggil saat penyimpanan Penerimaan. Trigger tetap berjalan otomatis pada INSERT kartu stok.

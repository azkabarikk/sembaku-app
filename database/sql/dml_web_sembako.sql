USE db_web_sembako;

-- 1. satuan
INSERT INTO satuan (idsatuan, nama_satuan, status)
VALUES
(1, 'Kilogram', 1),
(2, 'Liter', 1),
(3, 'Bungkus', 1),
(4, 'Botol', 1),
(5, 'Pcs', 1),
(6, 'Kotak', 1),
(7, 'Pak', 1);
SELECT * FROM satuan;

-- 2. vendor
INSERT INTO vendor
(idvendor, nama_vendor, badan_hukum, status)
VALUES
(1, 'UD Barokah Hasil Bumi', 'N', 'A'),
(2, 'CV Segar Pangan Surabaya', 'N', 'A'),
(3, 'CV Sumber Grosir Jatim', 'N', 'A'),
(4, 'PT Distribusi Pangan Nusantara', 'B', 'A'),
(5, 'PT Mitra Dagang Sejahtera', 'B', 'A'),
(6, 'PT Sinar Produk Indonesia', 'B', 'A');
SELECT * FROM vendor;

-- 3. role
INSERT INTO role (idrole, nama_role)
VALUES
(1, 'Admin'),
(2, 'Kasir'),
(3, 'Petugas Gudang');
SELECT * FROM role;

-- 4. barang
INSERT INTO barang
(idbarang, jenis, nama, idsatuan, status, harga)
VALUES
(1, 'S', 'Beras Sania Premium 1 Kg', 1, 1, 17000),
(2, 'S', 'Beras Ramos 1 Kg', 1, 1, 14500),
(3, 'S', 'Gula Rose Brand 1 Kg', 1, 1, 17500),
(4, 'S', 'Minyak Goreng Bimoli 1 L', 2, 1, 21000),
(5, 'S', 'Minyak Goreng Sania 1 L', 2, 1, 19500),
(6, 'S', 'Telur Ayam Negeri 1 Kg', 1, 1, 29000),
(7, 'S', 'Tepung Segitiga Biru 1 Kg', 1, 1, 14000),
(8, 'S', 'Garam Refina 500 Gr', 3, 1, 6500),
(9, 'S', 'Indomie Goreng 85 Gr', 3, 1, 3500),
(10, 'S', 'Indomie Soto 70 Gr', 3, 1, 3300),
(11, 'S', 'Kopi Kapal Api Special 6.5 Gr', 3, 1, 1500),
(12, 'S', 'Teh Celup SariWangi 25 Kantong', 6, 1, 8500),
(13, 'S', 'Ultra Milk Cokelat 200 Ml', 6, 1, 6500),
(14, 'S', 'Kecap Bango 135 Ml', 4, 1, 10000),
(15, 'S', 'Aqua 600 Ml', 4, 1, 4000),
(16, 'R', 'Sabun Lifebuoy 70 Gr', 5, 1, 4500),
(17, 'R', 'Rinso Bubuk 400 Gr', 3, 1, 10500),
(18, 'R', 'Sunlight Jeruk Nipis 400 Ml', 4, 1, 8500),
(19, 'R', 'Pepsodent 120 Gr', 5, 1, 12500),
(20, 'R', 'Paseo Tisu Wajah 200 Sheets', 7, 1, 14500);
SELECT * FROM barang;

-- 5. user
INSERT INTO user
(iduser, username, password, idrole)
VALUES
(1, 'bariq', 'bariqadmin123', 1),
(2, 'rangga.kasir', 'ranggakasir123', 2),
(3, 'reva.kasir', 'revakasir123', 2),
(4, 'safa.gudang', 'safagudang123', 3),
(5, 'seno.gudang', 'senogudang123', 3);
SELECT * FROM user;

-- 6. margin_penjualan
INSERT INTO margin_penjualan
(idmargin_penjualan, created_at, persen, status, iduser, updated_at)
VALUES
(1, CURRENT_TIMESTAMP, 10.0, 0, 1, CURRENT_TIMESTAMP),
(2, CURRENT_TIMESTAMP, 12.0, 1, 1, CURRENT_TIMESTAMP),
(3, CURRENT_TIMESTAMP, 15.0, 0, 1, CURRENT_TIMESTAMP);
SELECT * FROM margin_penjualan;


-- Jalankan setelah ddl_web_sembako.sql. Data awal dapat diisi lewat dml_web_sembako.sql.
USE db_web_sembako;

CREATE OR REPLACE VIEW v_master_satuan AS
SELECT idsatuan, nama_satuan, status
FROM satuan;

CREATE OR REPLACE VIEW v_master_vendor AS
SELECT idvendor, nama_vendor, badan_hukum, status
FROM vendor;

CREATE OR REPLACE VIEW v_master_role AS
SELECT idrole, nama_role
FROM `role`;

CREATE OR REPLACE VIEW v_master_barang AS
SELECT
    b.idbarang,
    b.jenis,
    b.nama,
    b.idsatuan,
    s.nama_satuan,
    b.harga,
    b.status
FROM barang AS b
JOIN satuan AS s ON s.idsatuan = b.idsatuan;

CREATE OR REPLACE VIEW v_master_user AS
SELECT u.iduser, u.username, u.idrole, r.nama_role
FROM `user` AS u
JOIN `role` AS r ON r.idrole = u.idrole;

CREATE OR REPLACE VIEW v_master_margin_penjualan AS
SELECT
    m.idmargin_penjualan,
    m.persen,
    m.status,
    m.iduser,
    u.username,
    m.created_at,
    m.updated_at
FROM margin_penjualan AS m
JOIN `user` AS u ON u.iduser = m.iduser;

-- Jumlah baris setelah impor DML awal: 7, 6, 3, 20, 5, dan 3.
SELECT 'v_master_satuan' AS nama_view, COUNT(*) AS jumlah_baris FROM v_master_satuan
UNION ALL
SELECT 'v_master_vendor', COUNT(*) FROM v_master_vendor
UNION ALL
SELECT 'v_master_role', COUNT(*) FROM v_master_role
UNION ALL
SELECT 'v_master_barang', COUNT(*) FROM v_master_barang
UNION ALL
SELECT 'v_master_user', COUNT(*) FROM v_master_user
UNION ALL
SELECT 'v_master_margin_penjualan', COUNT(*) FROM v_master_margin_penjualan;

<?php

namespace App\Queries\Transaksi;

use Illuminate\Support\Facades\DB;

class ReturQuery
{
    public function daftar(): array
    {
        return DB::select(
            'SELECT r.idretur, r.created_at, r.idpenerimaan, p.idpengadaan,
                    v.nama_vendor, u.username
             FROM retur r
             JOIN penerimaan p ON p.idpenerimaan = r.idpenerimaan
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = r.iduser
             ORDER BY r.idretur DESC'
        );
    }

    public function sumber(): array
    {
        return DB::select(
            "SELECT p.idpenerimaan, p.idpengadaan, p.created_at, p.status,
                    v.nama_vendor, u.username
             FROM penerimaan p
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.iduser
             WHERE p.status IN ('K', 'A')
               AND EXISTS (
                   SELECT 1 FROM detail_penerimaan d
                   JOIN kartu_stok k ON k.idbarang = d.barang_idbarang
                     AND k.idtransaksi = p.idpenerimaan AND k.jenis_transaksi IN ('M', 'P')
                   WHERE d.idpenerimaan = p.idpenerimaan
                     AND d.jumlah_terima > COALESCE((
                         SELECT SUM(dr.jumlah) FROM detail_retur dr
                         WHERE dr.iddetail_penerimaan = d.iddetail_penerimaan
                     ), 0)
               )
             ORDER BY p.idpenerimaan DESC"
        );
    }

    public function rincianSumber(int $id): array
    {
        return DB::select(
            'SELECT d.iddetail_penerimaan, d.barang_idbarang AS idbarang,
                    b.nama AS nama_barang, b.nama_satuan, d.jumlah_terima,
                    COALESCE(SUM(dr.jumlah), 0) AS jumlah_diretur,
                    d.jumlah_terima - COALESCE(SUM(dr.jumlah), 0) AS sisa_retur
             FROM detail_penerimaan d
             LEFT JOIN v_master_barang b ON b.idbarang = d.barang_idbarang
             LEFT JOIN detail_retur dr ON dr.iddetail_penerimaan = d.iddetail_penerimaan
             WHERE d.idpenerimaan = ?
             GROUP BY d.iddetail_penerimaan, d.barang_idbarang, b.nama, b.nama_satuan, d.jumlah_terima
             ORDER BY d.iddetail_penerimaan',
            [$id]
        );
    }

    public function headerSumber(int $id): ?object
    {
        return DB::selectOne(
            "SELECT p.idpenerimaan, p.idpengadaan, p.created_at, p.status,
                    v.nama_vendor, u.username
             FROM penerimaan p
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.iduser
             WHERE p.idpenerimaan = ?
               AND p.status IN ('K', 'A')
               AND EXISTS (
                   SELECT 1 FROM detail_penerimaan d
                   JOIN kartu_stok k ON k.idbarang = d.barang_idbarang
                     AND k.idtransaksi = p.idpenerimaan AND k.jenis_transaksi IN ('M', 'P')
                   WHERE d.idpenerimaan = p.idpenerimaan
                     AND d.jumlah_terima > COALESCE((
                         SELECT SUM(dr.jumlah) FROM detail_retur dr
                         WHERE dr.iddetail_penerimaan = d.iddetail_penerimaan
                     ), 0)
               )",
            [$id]
        );
    }

    public function header(int $id): ?object
    {
        return DB::selectOne(
            'SELECT r.idretur, r.created_at, r.idpenerimaan, p.idpengadaan,
                    v.nama_vendor, u.username
             FROM retur r
             JOIN penerimaan p ON p.idpenerimaan = r.idpenerimaan
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = r.iduser
             WHERE r.idretur = ?',
            [$id]
        );
    }

    public function rincian(int $id): array
    {
        return DB::select(
            'SELECT dr.iddetail_retur, dr.iddetail_penerimaan, dr.jumlah, dr.alasan,
                    d.barang_idbarang AS idbarang, b.nama AS nama_barang, b.nama_satuan
             FROM detail_retur dr
             JOIN detail_penerimaan d ON d.iddetail_penerimaan = dr.iddetail_penerimaan
             LEFT JOIN v_master_barang b ON b.idbarang = d.barang_idbarang
             WHERE dr.idretur = ? ORDER BY dr.iddetail_retur',
            [$id]
        );
    }
}

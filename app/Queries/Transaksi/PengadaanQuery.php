<?php

namespace App\Queries\Transaksi;

use Illuminate\Support\Facades\DB;

class PengadaanQuery
{
    public function daftarPengadaan(): array
    {
        return DB::select(
            'SELECT p.idpengadaan, p.timestamp AS dibuat, p.status,
                    p.subtotal_nilai, p.ppn, p.total_nilai,
                    v.nama_vendor, u.username,
                    EXISTS (
                        SELECT 1
                        FROM penerimaan pr
                        WHERE pr.idpengadaan = p.idpengadaan
                    ) AS punya_penerimaan
             FROM pengadaan p
             LEFT JOIN v_master_vendor v ON v.idvendor = p.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.user_iduser
             ORDER BY p.idpengadaan ASC'
        );
    }

    public function ringkasan(): array
    {
        $rincian = $this->rincianPengadaan();
        $ringkasan = [];

        foreach ($rincian as $id => $baris) {
            $ringkasan[$id] = [
                'dipesan' => array_sum(array_column($baris, 'jumlah')),
                'sisa' => array_sum(array_column($baris, 'jumlah_sisa')),
            ];
        }

        return $ringkasan;
    }

    public function headerDetail(int $id): ?object
    {
        return DB::selectOne(
            'SELECT p.idpengadaan, p.timestamp AS dibuat, p.status, p.subtotal_nilai, p.ppn, p.total_nilai,
                    v.nama_vendor, u.username
             FROM pengadaan p
             LEFT JOIN v_master_vendor v ON v.idvendor = p.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.user_iduser
             WHERE p.idpengadaan = ?',
            [$id]
        );
    }

    public function headerUntukEdit(int $id): ?object
    {
        return DB::selectOne(
            'SELECT idpengadaan, timestamp AS dibuat, user_iduser, status,
                    vendor_idvendor, subtotal_nilai, ppn, total_nilai
             FROM pengadaan
             WHERE idpengadaan = ?',
            [$id]
        );
    }

    public function detailUntukEdit(int $id): array
    {
        return DB::select(
            'SELECT idbarang, jumlah, harga_satuan
             FROM detail_pengadaan
             WHERE idpengadaan = ?
             ORDER BY iddetail_pengadaan',
            [$id]
        );
    }

    public function rincianPengadaan(?int $id = null): array
    {
        $daftarDetail = DB::select(
            'SELECT dp.idpengadaan, dp.iddetail_pengadaan, dp.idbarang, dp.jumlah,
                    COALESCE(terima.jumlah_diterima, 0) AS jumlah_diterima,
                    dp.harga_satuan, dp.sub_total, b.nama AS nama_barang, b.nama_satuan
             FROM detail_pengadaan dp
             LEFT JOIN v_master_barang b ON b.idbarang = dp.idbarang
             LEFT JOIN (
                 SELECT p.idpengadaan, d.barang_idbarang, SUM(d.jumlah_terima) AS jumlah_diterima
                 FROM detail_penerimaan d
                 JOIN penerimaan p ON p.idpenerimaan = d.idpenerimaan
                 WHERE p.status IN (?, ?) AND (? IS NULL OR p.idpengadaan = ?)
                 GROUP BY p.idpengadaan, d.barang_idbarang
             ) terima ON terima.idpengadaan = dp.idpengadaan AND terima.barang_idbarang = dp.idbarang
             WHERE (? IS NULL OR dp.idpengadaan = ?)
             ORDER BY dp.idpengadaan, dp.iddetail_pengadaan',
            ['A', 'K', $id, $id, $id, $id]
        );

        $detailPerPengadaan = [];
        $terimaTeralokasi = [];

        foreach ($daftarDetail as $detail) {
            $idPengadaan = $detail->idpengadaan;
            $barang = $detail->idbarang;
            $belumDialokasikan = (int) $detail->jumlah_diterima - ($terimaTeralokasi[$idPengadaan][$barang] ?? 0);
            $detail->jumlah_diterima = min((int) $detail->jumlah, max(0, $belumDialokasikan));
            $detail->jumlah_sisa = (int) $detail->jumlah - $detail->jumlah_diterima;
            $terimaTeralokasi[$idPengadaan][$barang] = ($terimaTeralokasi[$idPengadaan][$barang] ?? 0) + $detail->jumlah_diterima;
            $detailPerPengadaan[$idPengadaan][] = $detail;
        }

        return $detailPerPengadaan;
    }

    public function daftarVendorAktif(?int $vendorSaatIni = null): array
    {
        if ($vendorSaatIni !== null) {
            return DB::select(
                'SELECT idvendor, nama_vendor, status
                 FROM v_master_vendor
                 WHERE status = ? OR idvendor = ?
                 ORDER BY nama_vendor',
                ['A', $vendorSaatIni]
            );
        }

        return DB::select(
            'SELECT idvendor, nama_vendor
             FROM v_master_vendor
             WHERE status = ?
             ORDER BY nama_vendor',
            ['A']
        );
    }

    public function daftarBarangAktif(): array
    {
        return DB::select(
            'SELECT idbarang, nama, harga, status
             FROM v_master_barang
             WHERE status = ?
             ORDER BY nama',
            [1]
        );
    }

    public function daftarBarangUntukEdit(int $idPengadaan): array
    {
        return DB::select(
            'SELECT b.idbarang, b.nama, b.status, COALESCE((
                        SELECT d.harga_satuan
                        FROM detail_pengadaan d
                        WHERE d.idpengadaan = ? AND d.idbarang = b.idbarang
                        ORDER BY d.iddetail_pengadaan
                        LIMIT 1
                    ), b.harga) AS harga
             FROM v_master_barang b
             WHERE b.status = ?
                OR b.idbarang IN (
                    SELECT idbarang FROM detail_pengadaan WHERE idpengadaan = ?
                )
             ORDER BY b.nama',
            [$idPengadaan, 1, $idPengadaan]
        );
    }

    public function vendorDiizinkan(int $idVendor, ?int $vendorSaatIni): bool
    {
        return DB::selectOne(
            'SELECT idvendor FROM v_master_vendor WHERE idvendor = ? AND (status = ? OR idvendor = ?)',
            [$idVendor, 'A', $vendorSaatIni]
        ) !== null;
    }

    public function sudahMemilikiPenerimaan(int $idPengadaan): bool
    {
        return DB::selectOne(
            'SELECT idpenerimaan
             FROM penerimaan
             WHERE idpengadaan = ?
             LIMIT 1',
            [$idPengadaan]
        ) !== null;
    }
}

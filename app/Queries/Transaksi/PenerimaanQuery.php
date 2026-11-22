<?php

namespace App\Queries\Transaksi;

use Illuminate\Support\Facades\DB;

class PenerimaanQuery
{
    public function daftarPenerimaan(?int $idPengadaan): array
    {
        return DB::select(
            'SELECT p.idpenerimaan, p.created_at, p.status, p.idpengadaan, p.iduser,
                    u.username, v.nama_vendor
             FROM penerimaan p
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_user u ON u.iduser = p.iduser
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             WHERE (? IS NULL OR p.idpengadaan = ?)
             ORDER BY p.idpenerimaan ASC',
            [$idPengadaan, $idPengadaan]
        );
    }

    public function headerDetail(int $id): ?object
    {
        return DB::selectOne(
            'SELECT p.idpenerimaan, p.created_at, p.status, p.idpengadaan, p.iduser, v.nama_vendor, u.username
             FROM penerimaan p
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.iduser
             WHERE p.idpenerimaan = ?',
            [$id]
        );
    }

    public function detailPenerimaan(int $id, int $idPengadaan): array
    {
        return DB::select(
            'SELECT b.nama AS nama_barang, b.nama_satuan, COALESCE(pesanan.jumlah, 0) AS jumlah_dipesan,
                    d.jumlah_terima, d.harga_satuan_terima, d.sub_total_terima
             FROM detail_penerimaan d
             LEFT JOIN v_master_barang b ON b.idbarang = d.barang_idbarang
             LEFT JOIN (
                 SELECT idbarang, SUM(jumlah) AS jumlah
                 FROM detail_pengadaan WHERE idpengadaan = ? GROUP BY idbarang
             ) pesanan ON pesanan.idbarang = d.barang_idbarang
             WHERE d.idpenerimaan = ? ORDER BY d.iddetail_penerimaan',
            [$idPengadaan, $id]
        );
    }

    public function headerUntukEdit(int $id): ?object
    {
        return DB::selectOne(
            'SELECT idpenerimaan, created_at, status, idpengadaan, iduser FROM penerimaan WHERE idpenerimaan = ?',
            [$id]
        );
    }

    public function idPengadaanPenerimaan(int $id): int
    {
        $penerimaan = DB::selectOne('SELECT idpengadaan FROM penerimaan WHERE idpenerimaan = ?', [$id]);
        abort_if(! $penerimaan, 404);

        return (int) $penerimaan->idpengadaan;
    }

    public function rincianDraf(int $id): array
    {
        return array_column(DB::select(
            'SELECT barang_idbarang, jumlah_terima, harga_satuan_terima
             FROM detail_penerimaan WHERE idpenerimaan = ? ORDER BY iddetail_penerimaan',
            [$id]
        ), null, 'barang_idbarang');
    }

    public function pengadaanTersisa(): array
    {
        return DB::select(
            'SELECT p.idpengadaan, v.nama_vendor,
                    SUM(GREATEST(0, pesan.jumlah - COALESCE(terima.jumlah, 0))) AS jumlah_sisa
             FROM pengadaan p
             JOIN (
                 SELECT idpengadaan, idbarang, SUM(jumlah) AS jumlah
                 FROM detail_pengadaan
                 GROUP BY idpengadaan, idbarang
             ) pesan ON pesan.idpengadaan = p.idpengadaan
             LEFT JOIN (
                 SELECT r.idpengadaan, d.barang_idbarang, SUM(d.jumlah_terima) AS jumlah
                 FROM penerimaan r
                 JOIN detail_penerimaan d ON d.idpenerimaan = r.idpenerimaan
                 WHERE r.status IN (?, ?, ?)
                 GROUP BY r.idpengadaan, d.barang_idbarang
             ) terima ON terima.idpengadaan = p.idpengadaan AND terima.barang_idbarang = pesan.idbarang
             LEFT JOIN v_master_vendor v ON v.idvendor = p.vendor_idvendor
             WHERE p.status = ?
             GROUP BY p.idpengadaan, v.nama_vendor
             HAVING jumlah_sisa > 0
             ORDER BY p.idpengadaan ASC',
            ['A', 'K', 'D', 'A']
        );
    }

    public function pengadaanAktif(int $idpengadaan): ?object
    {
        return DB::selectOne(
            'SELECT p.idpengadaan, p.timestamp AS dibuat, p.vendor_idvendor,
                    v.nama_vendor
             FROM pengadaan p
             LEFT JOIN v_master_vendor v ON v.idvendor = p.vendor_idvendor
             WHERE p.idpengadaan = ?
             AND p.status = ?',
            [$idpengadaan, 'A']
        );
    }

    public function barisPesanan(int $idpengadaan, ?int $idPenerimaanDiabaikan = null, bool $hanyaTersisa = true): array
    {
        $barisPesanan = DB::select(
            'SELECT dp.idbarang, b.nama AS nama_barang,
                    SUM(dp.jumlah) AS jumlah_dipesan, MIN(dp.harga_satuan) AS harga_pesanan
             FROM detail_pengadaan dp
             JOIN v_master_barang b ON b.idbarang = dp.idbarang
             WHERE dp.idpengadaan = ?
             GROUP BY dp.idbarang, b.nama
             ORDER BY b.nama',
            [$idpengadaan]
        );

        $filterPenerimaanSaatIni = $idPenerimaanDiabaikan === null
            ? ''
            : 'AND p.idpenerimaan <> ?';
        $parameterPenerimaan = ['D', 'D', $idpengadaan, 'A', 'K', 'D'];

        if ($idPenerimaanDiabaikan !== null) {
            $parameterPenerimaan[] = $idPenerimaanDiabaikan;
        }

        $jumlahSudahDiterima = DB::select(
            "SELECT d.barang_idbarang AS idbarang,
                    SUM(CASE WHEN p.status <> ? THEN d.jumlah_terima ELSE 0 END) AS jumlah_diterima,
                    SUM(CASE WHEN p.status = ? THEN d.jumlah_terima ELSE 0 END) AS jumlah_draf
             FROM detail_penerimaan d
             JOIN penerimaan p ON p.idpenerimaan = d.idpenerimaan
             WHERE p.idpengadaan = ?
             AND p.status IN (?, ?, ?)
             {$filterPenerimaanSaatIni}
             GROUP BY d.barang_idbarang",
            $parameterPenerimaan
        );

        $diterimaPerBarang = array_column($jumlahSudahDiterima, null, 'idbarang');

        $barisTersisa = [];

        foreach ($barisPesanan as $baris) {
            $baris->jumlah_diterima = (int) ($diterimaPerBarang[$baris->idbarang]->jumlah_diterima ?? 0);
            $baris->jumlah_draf = (int) ($diterimaPerBarang[$baris->idbarang]->jumlah_draf ?? 0);
            $baris->jumlah_sisa = max(0, (int) $baris->jumlah_dipesan - $baris->jumlah_diterima - $baris->jumlah_draf);

            if (! $hanyaTersisa || $baris->jumlah_sisa > 0) {
                $barisTersisa[] = $baris;
            }
        }

        return $barisTersisa;
    }

    public function idTerakhir(int $idPengadaan): int
    {
        $baris = DB::selectOne('SELECT MAX(idpenerimaan) AS id FROM penerimaan WHERE idpengadaan = ?', [$idPengadaan]);

        return (int) $baris->id;
    }
}

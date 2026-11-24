<?php

namespace App\Queries\Transaksi;

use Illuminate\Support\Facades\DB;

class PenjualanQuery
{
    public function marginAktif(): ?object
    {
        $aktif = DB::select(
            'SELECT idmargin_penjualan, persen
             FROM margin_penjualan
             WHERE status = ?
             ORDER BY idmargin_penjualan DESC
             LIMIT 2',
            [1]
        );

        return count($aktif) === 1 ? $aktif[0] : null;
    }

    public function daftarBarang(string $persenMargin, string $cari = ''): array
    {
        return DB::select(
            'SELECT b.idbarang, b.nama, b.nama_satuan, stok.stok,
                    fn_harga_jual(b.harga, ?) AS harga_jual
             FROM v_master_barang b
             JOIN v_stok_barang stok ON stok.idbarang = b.idbarang
             WHERE b.status = ? AND (LOCATE(?, b.nama) > 0 OR b.idbarang = ?)
             ORDER BY b.nama, b.idbarang',
            [$persenMargin, 1, $cari, ctype_digit($cari) ? (int) $cari : null]
        );
    }
}

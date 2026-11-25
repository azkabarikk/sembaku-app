<?php

namespace App\Services\Transaksi;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturService
{
    /** @param array<int, array{iddetail_penerimaan: int, jumlah: int, alasan: string}> $items */
    public function simpan(int $idPenerimaan, int $idUser, array $items): int
    {
        return DB::transaction(function () use ($idPenerimaan, $idUser, $items): int {
            $penerimaan = DB::selectOne(
                'SELECT status FROM penerimaan WHERE idpenerimaan = ? FOR UPDATE',
                [$idPenerimaan]
            );
            if (! $penerimaan || ! in_array($penerimaan->status, ['K', 'A'], true)) {
                throw ValidationException::withMessages(['idpenerimaan' => 'Penerimaan belum sah untuk diretur.']);
            }

            $detailInput = array_column($items, null, 'iddetail_penerimaan');
            if (count($detailInput) !== count($items)) {
                throw ValidationException::withMessages(['items' => 'Barang retur tidak boleh berulang.']);
            }

            ksort($detailInput, SORT_NUMERIC);
            $rincian = [];
            foreach ($detailInput as $idDetail => $item) {
                $detail = DB::selectOne(
                    'SELECT iddetail_penerimaan, barang_idbarang, jumlah_terima
                     FROM detail_penerimaan
                     WHERE iddetail_penerimaan = ? AND idpenerimaan = ? FOR UPDATE',
                    [$idDetail, $idPenerimaan]
                );
                if (! $detail || (int) $detail->jumlah_terima < 1) {
                    throw ValidationException::withMessages(['items' => 'Barang harus berasal dari penerimaan yang sah.']);
                }

                $mutasiMasuk = DB::selectOne(
                    "SELECT idkartu_stok FROM kartu_stok
                     WHERE idbarang = ? AND idtransaksi = ? AND jenis_transaksi IN ('M', 'P')
                     LIMIT 1",
                    [$detail->barang_idbarang, $idPenerimaan]
                );
                if (! $mutasiMasuk) {
                    throw ValidationException::withMessages(['items' => 'Barang penerimaan belum tercatat di Kartu Stok.']);
                }

                $sudahDiretur = DB::selectOne(
                    'SELECT COALESCE(SUM(jumlah), 0) AS jumlah FROM detail_retur
                     WHERE iddetail_penerimaan = ?',
                    [$idDetail]
                );
                if ($item['jumlah'] > (int) $detail->jumlah_terima - (int) $sudahDiretur->jumlah) {
                    throw ValidationException::withMessages(['items' => 'Jumlah retur melebihi sisa barang yang dapat diretur.']);
                }

                $rincian[] = [
                    'iddetail_penerimaan' => $idDetail,
                    'idbarang' => (int) $detail->barang_idbarang,
                    'jumlah' => $item['jumlah'],
                    'alasan' => $item['alasan'],
                ];
            }

            $jumlahPerBarang = [];
            foreach ($rincian as $baris) {
                $jumlahPerBarang[$baris['idbarang']] = ($jumlahPerBarang[$baris['idbarang']] ?? 0) + $baris['jumlah'];
            }
            ksort($jumlahPerBarang, SORT_NUMERIC);
            foreach ($jumlahPerBarang as $idBarang => $jumlah) {
                DB::selectOne('SELECT idbarang FROM barang WHERE idbarang = ? FOR UPDATE', [$idBarang]);
                $saldo = DB::selectOne(
                    'SELECT COALESCE(SUM(masuk - keluar), 0) AS jumlah FROM kartu_stok WHERE idbarang = ?',
                    [$idBarang]
                );
                if ($jumlah > (int) $saldo->jumlah) {
                    throw ValidationException::withMessages(['items' => "Stok barang #{$idBarang} tidak cukup."]);
                }
            }

            DB::insert('INSERT INTO retur (idpenerimaan, iduser) VALUES (?, ?)', [$idPenerimaan, $idUser]);
            $idRetur = (int) DB::getPdo()->lastInsertId();
            if ($idRetur > 2147483647) {
                throw ValidationException::withMessages(['retur' => 'ID retur melebihi kapasitas referensi Kartu Stok.']);
            }

            foreach ($rincian as $baris) {
                DB::insert(
                    'INSERT INTO detail_retur (jumlah, alasan, idretur, iddetail_penerimaan) VALUES (?, ?, ?, ?)',
                    [$baris['jumlah'], $baris['alasan'], $idRetur, $baris['iddetail_penerimaan']]
                );
                DB::insert(
                    'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang)
                     VALUES (?, 0, ?, ?, ?)',
                    ['R', $baris['jumlah'], $idRetur, $baris['idbarang']]
                );
            }

            return $idRetur;
        }, 3);
    }
}

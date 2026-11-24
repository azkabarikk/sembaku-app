<?php

namespace App\Services\Transaksi;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenjualanService
{
    public const int PERSEN_PPN = 11;

    /**
     * @param  array{uang_diterima: int|string, items: array<int, array{idbarang: int|string, jumlah: int|string}>}  $input
     * @return array{idpenjualan: int, subtotal: int, ppn: int, total: int, uang_diterima: int, kembalian: int}
     */
    public function simpan(int $idUser, array $input): array
    {
        return DB::transaction(function () use ($idUser, $input): array {
            $margin = $this->kunciMarginAktif();
            $rincian = $this->siapkanRincian($input['items'], (string) $margin->persen);
            $uangDiterima = (int) $input['uang_diterima'];
            $kembalian = $uangDiterima - $rincian['total'];
            if ($kembalian < 0) {
                throw ValidationException::withMessages(['uang_diterima' => 'Uang diterima kurang dari total penjualan.']);
            }

            DB::insert(
                'INSERT INTO penjualan
                    (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $rincian['subtotal'],
                    $rincian['ppn'],
                    $rincian['total'],
                    $idUser,
                    $margin->idmargin_penjualan,
                ]
            );

            $idPenjualan = (int) DB::getPdo()->lastInsertId();

            foreach ($rincian['baris'] as $baris) {
                DB::insert(
                    'INSERT INTO detail_penjualan
                        (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
                     VALUES (?, ?, ?, ?, ?)',
                    [
                        $baris['harga_satuan'],
                        $baris['jumlah'],
                        $baris['subtotal'],
                        $idPenjualan,
                        $baris['idbarang'],
                    ]
                );

                DB::insert(
                    'INSERT INTO kartu_stok
                        (jenis_transaksi, masuk, keluar, idtransaksi, idbarang)
                     VALUES (?, 0, ?, ?, ?)',
                    ['J', $baris['jumlah'], $idPenjualan, $baris['idbarang']]
                );
            }

            return [
                'idpenjualan' => $idPenjualan,
                'subtotal' => $rincian['subtotal'],
                'ppn' => $rincian['ppn'],
                'total' => $rincian['total'],
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
            ];
        }, 3);
    }

    private function kunciMarginAktif(): object
    {
        $marginAktif = DB::select(
            'SELECT idmargin_penjualan, persen
             FROM margin_penjualan
             WHERE status = ?
             ORDER BY idmargin_penjualan
             FOR UPDATE',
            [1]
        );

        if (count($marginAktif) !== 1) {
            throw ValidationException::withMessages([
                'penjualan' => 'Penjualan memerlukan tepat satu margin aktif.',
            ]);
        }

        return $marginAktif[0];
    }

    /**
     * @param  array<int, array{idbarang: int|string, jumlah: int|string}>  $items
     * @return array{
     *     baris: array<int, array{idbarang: int, jumlah: int, harga_satuan: int, subtotal: int}>,
     *     subtotal: int,
     *     ppn: int,
     *     total: int
     * }
     */
    private function siapkanRincian(array $items, string $persenMargin): array
    {
        $jumlahPerBarang = [];

        foreach ($items as $item) {
            $idBarang = (int) $item['idbarang'];

            if (isset($jumlahPerBarang[$idBarang])) {
                throw ValidationException::withMessages(['items' => 'Barang yang sama tidak boleh dikirim lebih dari sekali.']);
            }

            $jumlahPerBarang[$idBarang] = (int) $item['jumlah'];
        }

        ksort($jumlahPerBarang, SORT_NUMERIC);
        $barisRincian = [];
        $subtotal = 0;

        foreach ($jumlahPerBarang as $idBarang => $jumlah) {
            $barang = DB::selectOne(
                'SELECT idbarang, nama, harga, status
                 FROM barang
                 WHERE idbarang = ?
                 FOR UPDATE',
                [$idBarang]
            );

            if (! $barang || (int) $barang->status !== 1) {
                throw ValidationException::withMessages(['items' => 'Barang tidak ditemukan atau tidak aktif.']);
            }

            $saldo = (int) DB::selectOne(
                'SELECT COALESCE(SUM(masuk - keluar), 0) AS jumlah
                 FROM kartu_stok
                 WHERE idbarang = ? FOR SHARE',
                [$idBarang]
            )->jumlah;

            if ($jumlah > $saldo) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$barang->nama} tidak cukup. Tersedia {$saldo}.",
                ]);
            }

            $hargaJual = (int) DB::selectOne(
                'SELECT fn_harga_jual(?, ?) AS harga_jual',
                [(int) $barang->harga, $persenMargin]
            )->harga_jual;
            $subtotalBaris = $jumlah * $hargaJual;

            if ($hargaJual <= 0 || $hargaJual > 2147483647 || $subtotalBaris > 2147483647 || $subtotal > 2147483647 - $subtotalBaris) {
                throw ValidationException::withMessages(['items' => 'Nilai penjualan melebihi batas yang didukung.']);
            }

            $subtotal += $subtotalBaris;
            $barisRincian[] = [
                'idbarang' => $idBarang,
                'jumlah' => $jumlah,
                'harga_satuan' => $hargaJual,
                'subtotal' => $subtotalBaris,
            ];
        }

        $ppn = intdiv($subtotal * self::PERSEN_PPN + 50, 100);
        $total = $subtotal + $ppn;

        if ($total > 2147483647) {
            throw ValidationException::withMessages(['items' => 'Total penjualan melebihi batas yang didukung.']);
        }

        return ['baris' => $barisRincian, 'subtotal' => $subtotal, 'ppn' => $ppn, 'total' => $total];
    }
}

<?php

namespace App\Services\Transaksi;

use App\Queries\Transaksi\PengadaanQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengadaanService
{
    public const int PERSEN_PPN = 11;

    public function __construct(private readonly PengadaanQuery $query) {}

    public function simpan(array $validated, int $idUser): int
    {
        return DB::transaction(function () use ($validated, $idUser): int {
            $detail = $this->siapkanDetailPengadaan($validated);

            DB::insert(
                'INSERT INTO pengadaan
                    (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $idUser,
                    'A',
                    $validated['vendor_idvendor'],
                    $detail['subtotal'],
                    $detail['ppn'],
                    $detail['total'],
                ]
            );

            $idPengadaan = (int) DB::getPdo()->lastInsertId();
            $this->simpanDetail($idPengadaan, $detail['baris']);

            return $idPengadaan;
        }, 3);
    }

    public function perbarui(int $id, array $validated): void
    {
        DB::transaction(function () use ($id, $validated): void {
            $pengadaan = $this->kunciPengadaan($id);

            if ($this->query->sudahMemilikiPenerimaan($id)) {
                throw ValidationException::withMessages(['pengadaan' => 'Pengadaan tidak dapat diubah setelah memiliki penerimaan.']);
            }

            $detail = $this->siapkanDetailPengadaan($validated, $id, (int) $pengadaan->vendor_idvendor);
            DB::update(
                'UPDATE pengadaan
                 SET status = ?, vendor_idvendor = ?,
                     subtotal_nilai = ?, ppn = ?, total_nilai = ?
                 WHERE idpengadaan = ?',
                [
                    $validated['status'],
                    $validated['vendor_idvendor'],
                    $detail['subtotal'],
                    $detail['ppn'],
                    $detail['total'],
                    $id,
                ]
            );

            DB::delete(
                'DELETE FROM detail_pengadaan
                 WHERE idpengadaan = ?',
                [$id]
            );
            $this->simpanDetail($id, $detail['baris']);
        }, 3);
    }

    public function hapus(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $this->kunciPengadaan($id);

            if ($this->query->sudahMemilikiPenerimaan($id)) {
                return false;
            }

            DB::delete(
                'DELETE FROM detail_pengadaan
                 WHERE idpengadaan = ?',
                [$id]
            );

            DB::delete(
                'DELETE FROM pengadaan
                 WHERE idpengadaan = ?',
                [$id]
            );

            return true;
        }, 3);
    }

    private function kunciPengadaan(int $id): object
    {
        $pengadaan = DB::selectOne('SELECT idpengadaan, vendor_idvendor FROM pengadaan WHERE idpengadaan = ? FOR UPDATE', [$id]);
        abort_if(! $pengadaan, 404);

        return $pengadaan;
    }

    private function simpanDetail(int $id, array $rincian): void
    {
        foreach ($rincian as $baris) {
            DB::insert(
                'INSERT INTO detail_pengadaan
                    (harga_satuan, jumlah, sub_total, idbarang, idpengadaan)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $baris['harga_satuan'],
                    $baris['jumlah'],
                    $baris['sub_total'],
                    $baris['idbarang'],
                    $id,
                ]
            );
        }
    }

    private function siapkanDetailPengadaan(array $validated, ?int $idPengadaan = null, ?int $vendorSaatIni = null): array
    {
        if (! $this->query->vendorDiizinkan((int) $validated['vendor_idvendor'], $vendorSaatIni)) {
            throw ValidationException::withMessages(['vendor_idvendor' => 'Vendor tidak ditemukan atau tidak aktif.']);
        }

        $idBarang = array_map('intval', array_values($validated['idbarang']));
        $jumlah = array_map('intval', array_values($validated['jumlah']));

        if (count($idBarang) !== count($jumlah)) {
            throw ValidationException::withMessages(['idbarang' => 'Rincian barang tidak lengkap.']);
        }

        $barang = $idPengadaan === null
            ? $this->query->daftarBarangAktif()
            : $this->query->daftarBarangUntukEdit($idPengadaan);
        $hargaPerBarang = array_column($barang, 'harga', 'idbarang');

        $baris = [];
        $subtotal = 0;

        foreach ($idBarang as $index => $barangId) {
            if (! isset($hargaPerBarang[$barangId])) {
                throw ValidationException::withMessages(['idbarang' => 'Barang tidak ditemukan atau tidak aktif.']);
            }

            $hargaSatuan = (int) $hargaPerBarang[$barangId];
            $subTotalBaris = (int) DB::selectOne(
                'SELECT fn_hitung_subtotal(?, ?) AS subtotal',
                [$jumlah[$index], $hargaSatuan]
            )->subtotal;

            if ($subTotalBaris < 0 || $subTotalBaris > 2147483647 || $subtotal > 2147483647 - $subTotalBaris) {
                throw ValidationException::withMessages(['idbarang' => 'Subtotal pengadaan melebihi batas nilai yang didukung.']);
            }

            $subtotal += $subTotalBaris;
            $baris[] = [
                'idbarang' => $barangId,
                'jumlah' => $jumlah[$index],
                'harga_satuan' => $hargaSatuan,
                'sub_total' => $subTotalBaris,
            ];
        }

        $ppn = (int) DB::selectOne(
            'SELECT fn_hitung_ppn(?) AS ppn',
            [$subtotal]
        )->ppn;
        $total = $subtotal + $ppn;

        if ($total > 2147483647) {
            throw ValidationException::withMessages(['idbarang' => 'Total pengadaan melebihi batas nilai yang didukung.']);
        }

        return ['baris' => $baris, 'subtotal' => $subtotal, 'ppn' => $ppn, 'total' => $total];
    }
}

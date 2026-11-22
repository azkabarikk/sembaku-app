<?php

namespace App\Services\Transaksi;

use App\Queries\Transaksi\PenerimaanQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PenerimaanService
{
    public function __construct(private readonly PenerimaanQuery $query) {}

    public function simpan(int $idPengadaan, int $idUser, array $input): int
    {
        return DB::transaction(function () use ($idPengadaan, $idUser, $input): int {
            $pengadaan = $this->kunciPengadaan($idPengadaan);
            abort_if($pengadaan->status !== 'A', 404);
            $this->kunciBarangAktif($input['jumlah_terima']);

            if (isset($input['penerimaan_terakhir']) && (int) $input['penerimaan_terakhir'] !== $this->query->idTerakhir($idPengadaan)) {
                throw ValidationException::withMessages(['penerimaan' => 'Penerimaan lain sudah disimpan. Periksa kembali sisa pesanan.']);
            }

            $rincian = $this->rincianUntukSimpan($idPengadaan, $input['jumlah_terima'], $input['harga_satuan_terima'] ?? []);
            DB::insert('INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)', ['D', $idPengadaan, $idUser]);
            $id = (int) DB::getPdo()->lastInsertId();
            $this->pastikanBelumDibukukan($id);
            $this->bukukanRincian($id, $rincian);

            return $id;
        }, 3);
    }

    public function perbaruiDraf(int $id, array $input): void
    {
        $idPengadaan = $this->query->idPengadaanPenerimaan($id);

        DB::transaction(function () use ($id, $idPengadaan, $input): void {
            $pengadaan = $this->kunciPengadaan($idPengadaan);
            $this->otorisasiDrafTerkunci($id);
            abort_if($pengadaan->status !== 'A', 409, 'Pengadaan sedang ditahan.');
            $this->kunciBarangAktif($input['jumlah_terima']);
            $this->pastikanBelumDibukukan($id);
            $this->pastikanBelumDiretur($id);

            $rincian = $this->rincianUntukSimpan($idPengadaan, $input['jumlah_terima'], $input['harga_satuan_terima'] ?? [], $id);
            DB::delete('DELETE FROM detail_penerimaan WHERE idpenerimaan = ?', [$id]);
            $this->bukukanRincian($id, $rincian);
        }, 3);
    }

    public function hapusDraf(int $id): void
    {
        $idPengadaan = $this->query->idPengadaanPenerimaan($id);

        DB::transaction(function () use ($id, $idPengadaan): void {
            $this->kunciPengadaan($idPengadaan);
            $this->otorisasiDrafTerkunci($id);
            $this->pastikanBelumDibukukan($id);
            $this->pastikanBelumDiretur($id);

            DB::delete('DELETE FROM detail_penerimaan WHERE idpenerimaan = ?', [$id]);
            DB::delete('DELETE FROM penerimaan WHERE idpenerimaan = ?', [$id]);
        }, 3);
    }

    private function kunciBarangAktif(array $jumlahInput): void
    {
        // Kunci menurut ID sebelum membaca sisa dan saldo agar transaksi antar-pengadaan melihat stok terbaru.
        ksort($jumlahInput, SORT_NUMERIC);
        foreach ($jumlahInput as $idBarang => $jumlah) {
            if ((int) $jumlah === 0) {
                continue;
            }

            $barang = DB::selectOne('SELECT status FROM barang WHERE idbarang = ? FOR UPDATE', [$idBarang]);
            if (! $barang || (int) $barang->status !== 1) {
                throw ValidationException::withMessages(['penerimaan' => 'Barang penerimaan tidak ditemukan atau tidak aktif.']);
            }
        }
    }

    private function pastikanBelumDibukukan(int $id): void
    {
        $mutasi = DB::selectOne(
            'SELECT idkartu_stok FROM kartu_stok WHERE jenis_transaksi IN (?, ?) AND idtransaksi = ? LIMIT 1',
            ['M', 'P', $id]
        );

        if ($mutasi) {
            throw ValidationException::withMessages(['penerimaan' => 'Kartu Stok sudah memiliki mutasi masuk untuk penerimaan ini.']);
        }
    }

    /** @param array<int, array{jumlah: int, harga_satuan: int, subtotal: int}> $rincian */
    private function bukukanRincian(int $id, array $rincian): void
    {
        if ($id > 2147483647) {
            throw ValidationException::withMessages(['penerimaan' => 'ID penerimaan melebihi kapasitas referensi Kartu Stok.']);
        }

        foreach ($rincian as $idBarang => $baris) {
            $saldo = DB::selectOne(
                'SELECT COALESCE(SUM(masuk - keluar), 0) AS jumlah FROM kartu_stok WHERE idbarang = ?',
                [$idBarang]
            );
            if ((int) $saldo->jumlah < 0 || (int) $saldo->jumlah > 2147483647 - $baris['jumlah']) {
                throw ValidationException::withMessages(['penerimaan' => "Saldo stok barang #{$idBarang} di luar batas yang didukung."]);
            }

            $this->simpanDetail($id, $idBarang, $baris);
            DB::insert(
                'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang) VALUES (?, ?, 0, ?, ?)',
                ['M', $baris['jumlah'], $id, $idBarang]
            );
            DB::update('UPDATE barang SET harga = ? WHERE idbarang = ?', [$baris['harga_satuan'], $idBarang]);
        }

        DB::update('UPDATE penerimaan SET status = ? WHERE idpenerimaan = ?', ['K', $id]);
    }

    private function kunciPengadaan(int $idPengadaan): object
    {
        $pengadaan = DB::selectOne(
            'SELECT status FROM pengadaan WHERE idpengadaan = ? FOR UPDATE',
            [$idPengadaan]
        );
        abort_if(! $pengadaan, 404);

        return $pengadaan;
    }

    private function otorisasiDrafTerkunci(int $id): void
    {
        $penerimaan = DB::selectOne(
            'SELECT iduser, status FROM penerimaan WHERE idpenerimaan = ? FOR UPDATE',
            [$id]
        );
        abort_if(! $penerimaan, 404);
        Gate::authorize('penerimaan.ubah-draf', $penerimaan);
    }

    /**
     * @param  array<int, int|string>  $jumlahInput
     * @param  array<int, int|string|null>  $hargaInput
     * @return array<int, array{jumlah: int, harga_satuan: int, subtotal: int}>
     */
    private function rincianUntukSimpan(int $idPengadaan, array $jumlahInput, array $hargaInput = [], ?int $idPenerimaan = null): array
    {
        $pesanan = array_column($this->query->barisPesanan($idPengadaan, $idPenerimaan, hanyaTersisa: false), null, 'idbarang');
        $detailTercatat = $idPenerimaan === null ? [] : $this->query->rincianDraf($idPenerimaan);
        $rincian = [];

        foreach ($jumlahInput as $idBarang => $jumlah) {
            if (! isset($pesanan[$idBarang])) {
                throw ValidationException::withMessages([
                    'jumlah_terima' => 'Barang yang diterima harus berasal dari pengadaan ini.',
                ]);
            }

            $jumlah = (int) $jumlah;
            $baris = $pesanan[$idBarang];

            if ($jumlah > 0 && $baris->jumlah_sisa === 0) {
                throw ValidationException::withMessages([
                    'jumlah_terima' => "Pesanan untuk {$baris->nama_barang} sudah diterima seluruhnya.",
                ]);
            }

            if ($jumlah > $baris->jumlah_sisa) {
                throw ValidationException::withMessages([
                    "jumlah_terima.{$idBarang}" => "Jumlah melebihi sisa pesanan ({$baris->jumlah_sisa}).",
                ]);
            }

            if ($jumlah === 0) {
                continue;
            }

            $harga = (int) ($hargaInput[$idBarang] ?? $detailTercatat[$idBarang]->harga_satuan_terima ?? $baris->harga_pesanan);
            $subtotal = $jumlah * $harga;

            if ($harga < 0 || $subtotal > 2147483647) {
                throw ValidationException::withMessages([
                    "jumlah_terima.{$idBarang}" => 'Subtotal barang di luar batas nilai yang didukung.',
                ]);
            }

            $rincian[$idBarang] = ['jumlah' => $jumlah, 'harga_satuan' => $harga, 'subtotal' => $subtotal];
        }

        if ($rincian === []) {
            throw ValidationException::withMessages(['jumlah_terima' => 'Isi jumlah minimal satu barang yang diterima.']);
        }

        return $rincian;
    }

    private function pastikanBelumDiretur(int $id): void
    {
        $retur = DB::selectOne(
            'SELECT idretur FROM retur WHERE idpenerimaan = ? LIMIT 1 FOR SHARE',
            [$id]
        );

        $detailRetur = DB::selectOne(
            'SELECT dr.iddetail_retur
             FROM detail_retur dr
             JOIN detail_penerimaan d ON d.iddetail_penerimaan = dr.iddetail_penerimaan
             WHERE d.idpenerimaan = ?
             LIMIT 1 FOR SHARE',
            [$id]
        );

        if ($retur || $detailRetur) {
            throw ValidationException::withMessages(['penerimaan' => 'Rincian penerimaan sudah dipakai pada retur.']);
        }
    }

    /** @param array{jumlah: int, harga_satuan: int, subtotal: int} $baris */
    private function simpanDetail(int $id, int $idBarang, array $baris): void
    {
        DB::insert(
            'INSERT INTO detail_penerimaan
                (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
             VALUES (?, ?, ?, ?, ?)',
            [$id, $idBarang, $baris['jumlah'], $baris['harga_satuan'], $baris['subtotal']]
        );
    }
}

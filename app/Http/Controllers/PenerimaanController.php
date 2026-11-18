<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenerimaanController extends Controller
{
    public function index(Request $request): View
    {
        $idPengadaan = filter_var($request->query('pengadaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        $daftarPenerimaan = DB::select(
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

        return view('penerimaan.index', [
            'daftarPenerimaan' => $daftarPenerimaan,
            'idPengadaan' => $idPengadaan,
            'pengadaanTersisa' => $this->pengadaanTersisa(),
        ]);
    }

    public function pilihPengadaan(Request $request): RedirectResponse
    {
        $id = filter_var($request->query('pengadaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id === false) {
            return redirect()->route('penerimaan.index')->with('error', 'Pilih pengadaan yang akan diterima.');
        }

        return redirect()->route('penerimaan.create', $id);
    }

    public function create(int $idpengadaan): View|RedirectResponse
    {
        $pengadaan = $this->pengadaanAktif($idpengadaan);

        if (! $pengadaan) {
            abort(404);
        }

        $barisPesanan = $this->barisPesanan($idpengadaan);

        if ($barisPesanan === []) {
            return redirect()
                ->route('penerimaan.index')
                ->with('error', 'Pengadaan ini tidak memiliki sisa barang untuk diterima.');
        }

        return view('penerimaan.create', [
            'pengadaan' => $pengadaan,
            'barisPesanan' => $barisPesanan,
        ]);
    }

    public function store(Request $request, int $idpengadaan): RedirectResponse
    {
        $validated = $this->validasiPenerimaan($request);
        $idUser = (int) $request->user()->getAuthIdentifier();

        $id = DB::transaction(function () use ($idpengadaan, $validated, $idUser): int {
            $pengadaan = $this->kunciPengadaan($idpengadaan);
            abort_if($pengadaan->status !== 'A', 404);

            $barangDiterima = $this->rincianUntukSimpan(
                $idpengadaan,
                $validated['jumlah_terima'],
                $validated['harga_satuan_terima'] ?? []
            );

            DB::insert(
                'INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)',
                ['D', $idpengadaan, $idUser]
            );

            $id = (int) DB::getPdo()->lastInsertId();

            foreach ($barangDiterima as $idBarang => $baris) {
                $this->simpanDetail($id, $idBarang, $baris);
            }

            return $id;
        }, 3);

        return redirect()->route('penerimaan.show', $id)->with('success', 'Draf penerimaan berhasil disimpan.');
    }

    public function show(int $id): View
    {
        $penerimaan = DB::selectOne(
            'SELECT p.idpenerimaan, p.created_at, p.status, p.idpengadaan, p.iduser, v.nama_vendor, u.username
             FROM penerimaan p
             JOIN pengadaan pg ON pg.idpengadaan = p.idpengadaan
             LEFT JOIN v_master_vendor v ON v.idvendor = pg.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.iduser
             WHERE p.idpenerimaan = ?',
            [$id]
        );
        abort_if(! $penerimaan, 404);

        $rincian = DB::select(
            'SELECT b.nama AS nama_barang, b.nama_satuan, COALESCE(pesanan.jumlah, 0) AS jumlah_dipesan,
                    d.jumlah_terima, d.harga_satuan_terima, d.sub_total_terima
             FROM detail_penerimaan d
             LEFT JOIN v_master_barang b ON b.idbarang = d.barang_idbarang
             LEFT JOIN (
                 SELECT idbarang, SUM(jumlah) AS jumlah
                 FROM detail_pengadaan WHERE idpengadaan = ? GROUP BY idbarang
             ) pesanan ON pesanan.idbarang = d.barang_idbarang
             WHERE d.idpenerimaan = ? ORDER BY d.iddetail_penerimaan',
            [$penerimaan->idpengadaan, $id]
        );

        return view('penerimaan.show', ['penerimaan' => $penerimaan, 'rincian' => $rincian]);
    }

    public function edit(int $id): View
    {
        $penerimaan = DB::selectOne(
            'SELECT idpenerimaan, created_at, status, idpengadaan, iduser FROM penerimaan WHERE idpenerimaan = ?',
            [$id]
        );
        abort_if(! $penerimaan, 404);
        Gate::authorize('penerimaan.ubah-draf', $penerimaan);

        return view('penerimaan.edit', [
            'penerimaan' => $penerimaan,
            'barisPesanan' => $this->barisPesanan((int) $penerimaan->idpengadaan, $id),
            'rincianDraf' => $this->rincianDraf($id),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $this->validasiPenerimaan($request);
        $idPengadaan = $this->idPengadaanPenerimaan($id);

        DB::transaction(function () use ($id, $idPengadaan, $validated): void {
            $pengadaan = $this->kunciPengadaan($idPengadaan);
            $this->otorisasiDrafTerkunci($id);
            abort_if($pengadaan->status !== 'A', 409, 'Pengadaan sedang ditahan.');

            $barangDiterima = $this->rincianUntukSimpan(
                $idPengadaan,
                $validated['jumlah_terima'],
                $validated['harga_satuan_terima'] ?? [],
                $id
            );

            $this->pastikanBelumDiretur($id);
            DB::delete('DELETE FROM detail_penerimaan WHERE idpenerimaan = ?', [$id]);

            foreach ($barangDiterima as $idBarang => $baris) {
                $this->simpanDetail($id, $idBarang, $baris);
            }
        }, 3);

        return redirect()->route('penerimaan.show', $id)->with('success', 'Draf penerimaan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $idPengadaan = $this->idPengadaanPenerimaan($id);

        DB::transaction(function () use ($id, $idPengadaan): void {
            $this->kunciPengadaan($idPengadaan);
            $this->otorisasiDrafTerkunci($id);
            $this->pastikanBelumDiretur($id);

            DB::delete('DELETE FROM detail_penerimaan WHERE idpenerimaan = ?', [$id]);
            DB::delete('DELETE FROM penerimaan WHERE idpenerimaan = ?', [$id]);
        }, 3);

        return redirect()->route('penerimaan.index')->with('success', 'Draf penerimaan berhasil dihapus.');
    }

    public function confirm(int $id): RedirectResponse
    {
        $idPengadaan = $this->idPengadaanPenerimaan($id);

        try {
            DB::transaction(function () use ($id, $idPengadaan): void {
                // Urutan kunci pengadaan lalu penerimaan sama dengan edit dan hapus draf.
                $pengadaan = $this->kunciPengadaan($idPengadaan);
                $penerimaan = DB::selectOne(
                    'SELECT idpengadaan, status FROM penerimaan WHERE idpenerimaan = ? FOR UPDATE',
                    [$id]
                );
                abort_if(! $penerimaan, 404);

                $this->syaratKonfirmasi($penerimaan->status === 'D', 'Hanya penerimaan berstatus Draf yang bisa dikonfirmasi.');
                $this->syaratKonfirmasi((int) $penerimaan->idpengadaan === $idPengadaan && $pengadaan->status === 'A', 'Pengadaan tidak siap diterima.');
                $this->syaratKonfirmasi($id <= 2147483647, 'ID penerimaan melebihi kapasitas referensi Kartu Stok.');

                $rincian = DB::select(
                    'SELECT barang_idbarang AS idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima
                     FROM detail_penerimaan WHERE idpenerimaan = ?
                     ORDER BY barang_idbarang, iddetail_penerimaan FOR UPDATE',
                    [$id]
                );
                $this->syaratKonfirmasi($rincian !== [], 'Detail penerimaan masih kosong.');

                $barangTerkunci = [];

                foreach ($rincian as $detail) {
                    $idBarang = (int) $detail->idbarang;
                    $jumlah = (int) $detail->jumlah_terima;
                    $harga = (int) $detail->harga_satuan_terima;

                    $this->syaratKonfirmasi(! isset($barangTerkunci[$idBarang]), 'Barang duplikat pada detail penerimaan.');
                    $this->syaratKonfirmasi($jumlah > 0, 'Jumlah terima tidak valid atau melebihi pesanan.');
                    $this->syaratKonfirmasi(
                        $harga >= 0 && $jumlah * $harga === (int) $detail->sub_total_terima,
                        'Harga atau subtotal penerimaan tidak valid.'
                    );

                    $barang = DB::selectOne('SELECT status FROM barang WHERE idbarang = ? FOR UPDATE', [$idBarang]);
                    $this->syaratKonfirmasi($barang !== null && (int) $barang->status === 1, 'Barang penerimaan tidak ditemukan atau tidak aktif.');
                    $barangTerkunci[$idBarang] = true;
                }

                // Baca stok setelah semua barang dikunci agar snapshot juga segar untuk trigger sederhana.
                $this->validasiKonfirmasi($id, $idPengadaan, $rincian);

                foreach ($rincian as $detail) {
                    DB::insert(
                        'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang)
                         VALUES (?, ?, 0, ?, ?)',
                        ['M', $detail->jumlah_terima, $id, $detail->idbarang]
                    );

                    DB::update(
                        'UPDATE barang SET harga = ? WHERE idbarang = ?',
                        [$detail->harga_satuan_terima, $detail->idbarang]
                    );
                }

                DB::update('UPDATE penerimaan SET status = ? WHERE idpenerimaan = ?', ['K', $id]);
            }, 3);
        } catch (ValidationException $exception) {
            return redirect()->route('penerimaan.show', $id)->with('error', $exception->getMessage());
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->route('penerimaan.show', $id)->with('error', 'Penerimaan gagal dikonfirmasi. Perubahan dibatalkan.');
        }

        return redirect()->route('penerimaan.show', $id)->with('success', 'Penerimaan berhasil dikonfirmasi.');
    }

    /** @param array<int, object> $rincian */
    private function validasiKonfirmasi(int $id, int $idPengadaan, array $rincian): void
    {
        $mutasi = DB::selectOne(
            'SELECT idkartu_stok FROM kartu_stok WHERE jenis_transaksi IN (?, ?) AND idtransaksi = ? LIMIT 1',
            ['M', 'P', $id]
        );
        $this->syaratKonfirmasi($mutasi === null, 'Kartu Stok sudah memiliki mutasi masuk untuk penerimaan ini.');

        $jumlahDipesan = array_column(DB::select(
            'SELECT idbarang, SUM(jumlah) AS jumlah FROM detail_pengadaan
             WHERE idpengadaan = ? GROUP BY idbarang',
            [$idPengadaan]
        ), 'jumlah', 'idbarang');

        $jumlahDiterima = array_column(DB::select(
            'SELECT d.barang_idbarang, SUM(d.jumlah_terima) AS jumlah
             FROM detail_penerimaan d JOIN penerimaan p ON p.idpenerimaan = d.idpenerimaan
             WHERE p.idpengadaan = ? AND p.status IN (?, ?) GROUP BY d.barang_idbarang',
            [$idPengadaan, 'K', 'A']
        ), 'jumlah', 'barang_idbarang');

        foreach ($rincian as $detail) {
            $idBarang = (int) $detail->idbarang;
            $jumlah = (int) $detail->jumlah_terima;
            $sisa = (int) ($jumlahDipesan[$idBarang] ?? 0) - (int) ($jumlahDiterima[$idBarang] ?? 0);
            $this->syaratKonfirmasi($jumlah <= $sisa, 'Jumlah terima tidak valid atau melebihi pesanan.');

            $saldo = DB::selectOne(
                'SELECT COALESCE(SUM(masuk - keluar), 0) AS jumlah FROM kartu_stok WHERE idbarang = ?',
                [$idBarang]
            );
            $this->syaratKonfirmasi(
                (int) $saldo->jumlah >= 0 && (int) $saldo->jumlah <= 2147483647 - $jumlah,
                "Saldo stok barang #{$idBarang} di luar batas yang didukung."
            );
        }
    }

    private function syaratKonfirmasi(bool $terpenuhi, string $pesan): void
    {
        if (! $terpenuhi) {
            throw ValidationException::withMessages(['penerimaan' => $pesan]);
        }
    }

    /** @return array{jumlah_terima: array<int, int|string>, harga_satuan_terima?: array<int, int|string|null>} */
    private function validasiPenerimaan(Request $request): array
    {
        return $request->validate([
            'jumlah_terima' => 'required|array',
            'jumlah_terima.*' => 'required|integer|min:0|max:2147483647',
            'harga_satuan_terima' => 'sometimes|array',
            'harga_satuan_terima.*' => 'nullable|integer|min:0|max:2147483647',
        ]);
    }

    private function idPengadaanPenerimaan(int $id): int
    {
        $penerimaan = DB::selectOne('SELECT idpengadaan FROM penerimaan WHERE idpenerimaan = ?', [$id]);
        abort_if(! $penerimaan, 404);

        return (int) $penerimaan->idpengadaan;
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

    private function rincianDraf(int $id): array
    {
        return array_column(DB::select(
            'SELECT barang_idbarang, jumlah_terima, harga_satuan_terima
             FROM detail_penerimaan WHERE idpenerimaan = ? ORDER BY iddetail_penerimaan',
            [$id]
        ), null, 'barang_idbarang');
    }

    /**
     * @param  array<int, int|string>  $jumlahInput
     * @param  array<int, int|string|null>  $hargaInput
     * @return array<int, array{jumlah: int, harga_satuan: int, subtotal: int}>
     */
    private function rincianUntukSimpan(int $idPengadaan, array $jumlahInput, array $hargaInput = [], ?int $idPenerimaan = null): array
    {
        $pesanan = array_column($this->barisPesanan($idPengadaan, $idPenerimaan, hanyaTersisa: false), null, 'idbarang');
        $detailTercatat = $idPenerimaan === null ? [] : $this->rincianDraf($idPenerimaan);
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

    private function pengadaanTersisa(): array
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

    private function pengadaanAktif(int $idpengadaan): ?object
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

    private function barisPesanan(int $idpengadaan, ?int $idPenerimaanDiabaikan = null, bool $hanyaTersisa = true): array
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

        $diterimaPerBarang = [];

        foreach ($jumlahSudahDiterima as $baris) {
            $diterimaPerBarang[$baris->idbarang] = $baris;
        }

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
}

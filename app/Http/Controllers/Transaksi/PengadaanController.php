<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengadaanController extends Controller
{
    private const int PERSEN_PPN = 11;

    public function index(): View
    {
        $daftarPengadaan = DB::select(
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

        $rincian = $this->rincianPengadaan();
        $ringkasan = [];

        foreach ($rincian as $id => $baris) {
            $ringkasan[$id] = [
                'dipesan' => array_sum(array_column($baris, 'jumlah')),
                'sisa' => array_sum(array_column($baris, 'jumlah_sisa')),
            ];
        }

        return view('pengadaan.index', [
            'daftarPengadaan' => $daftarPengadaan,
            'ringkasan' => $ringkasan,
        ]);
    }

    public function show(int $id): View
    {
        $pengadaan = DB::selectOne(
            'SELECT p.idpengadaan, p.timestamp AS dibuat, p.status, p.subtotal_nilai, p.ppn, p.total_nilai,
                    v.nama_vendor, u.username
             FROM pengadaan p
             LEFT JOIN v_master_vendor v ON v.idvendor = p.vendor_idvendor
             LEFT JOIN v_master_user u ON u.iduser = p.user_iduser
             WHERE p.idpengadaan = ?',
            [$id]
        );

        abort_if(! $pengadaan, 404);

        return view('pengadaan.show', [
            'pengadaan' => $pengadaan,
            'rincian' => $this->rincianPengadaan($id)[$id] ?? [],
        ]);
    }

    public function create(): View
    {
        return view('pengadaan.create', [
            'daftarVendor' => $this->daftarVendorAktif(),
            'persenPpn' => self::PERSEN_PPN,
            'daftarBarang' => $this->daftarBarangAktif(),
            'barisDetail' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->aturanValidasi());
        $idUser = (int) $request->user()->getAuthIdentifier();
        $errors = $this->validasiRelasi($validated);

        if ($errors !== []) {
            return back()->withErrors($errors)->withInput();
        }

        $hasilDetail = $this->siapkanDetailPengadaan($validated);

        if ($hasilDetail['error'] !== null) {
            return back()->withErrors(['idbarang' => $hasilDetail['error']])->withInput();
        }

        $subtotal = $hasilDetail['subtotal'];
        $ppn = intdiv($subtotal * self::PERSEN_PPN + 50, 100);
        $total = $subtotal + $ppn;

        if ($total > 2147483647) {
            return back()
                ->withErrors(['idbarang' => 'Total pengadaan melebihi batas nilai yang didukung.'])
                ->withInput();
        }

        $idPengadaan = DB::transaction(function () use ($validated, $idUser, $hasilDetail, $subtotal, $ppn, $total): int {
            DB::insert(
                'INSERT INTO pengadaan
                    (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $idUser,
                    'A',
                    $validated['vendor_idvendor'],
                    $subtotal,
                    $ppn,
                    $total,
                ]
            );

            $idPengadaan = (int) DB::getPdo()->lastInsertId();

            foreach ($hasilDetail['baris'] as $baris) {
                DB::insert(
                    'INSERT INTO detail_pengadaan
                        (harga_satuan, jumlah, sub_total, idbarang, idpengadaan)
                     VALUES (?, ?, ?, ?, ?)',
                    [
                        $baris['harga_satuan'],
                        $baris['jumlah'],
                        $baris['sub_total'],
                        $baris['idbarang'],
                        $idPengadaan,
                    ]
                );
            }

            return $idPengadaan;
        }, 3);

        return redirect()
            ->route('pengadaan.show', $idPengadaan)
            ->with('success', 'Pengadaan berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $pengadaan = DB::selectOne(
            'SELECT idpengadaan, timestamp AS dibuat, user_iduser, status,
                    vendor_idvendor, subtotal_nilai, ppn, total_nilai
             FROM pengadaan
             WHERE idpengadaan = ?',
            [$id]
        );

        if (! $pengadaan) {
            abort(404);
        }

        if ($this->sudahMemilikiPenerimaan($id)) {
            abort(403, 'Pengadaan tidak dapat diubah setelah memiliki penerimaan.');
        }

        $barisDetail = DB::select(
            'SELECT idbarang, jumlah, harga_satuan
             FROM detail_pengadaan
             WHERE idpengadaan = ?
             ORDER BY iddetail_pengadaan',
            [$id]
        );

        return view('pengadaan.edit', [
            'pengadaan' => $pengadaan,
            'daftarVendor' => $this->daftarVendorAktif((int) $pengadaan->vendor_idvendor),
            'persenPpn' => self::PERSEN_PPN,
            'daftarBarang' => $this->daftarBarangUntukEdit($id),
            'barisDetail' => $barisDetail,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->aturanValidasi(),
            'status' => 'required|in:A,N',
        ]);

        return DB::transaction(function () use ($validated, $id): RedirectResponse {
            $pengadaan = DB::selectOne(
                'SELECT idpengadaan, vendor_idvendor
                 FROM pengadaan
                 WHERE idpengadaan = ? FOR UPDATE',
                [$id]
            );

            if (! $pengadaan) {
                abort(404);
            }

            if ($this->sudahMemilikiPenerimaan($id)) {
                return back()
                    ->withErrors(['pengadaan' => 'Pengadaan tidak dapat diubah setelah memiliki penerimaan.'])
                    ->withInput();
            }

            $errors = $this->validasiRelasi($validated, (int) $pengadaan->vendor_idvendor);

            if ($errors !== []) {
                return back()->withErrors($errors)->withInput();
            }

            $hasilDetail = $this->siapkanDetailPengadaan($validated, $id);

            if ($hasilDetail['error'] !== null) {
                return back()->withErrors(['idbarang' => $hasilDetail['error']])->withInput();
            }

            $subtotal = $hasilDetail['subtotal'];
            $ppn = intdiv($subtotal * self::PERSEN_PPN + 50, 100);
            $total = $subtotal + $ppn;

            if ($total > 2147483647) {
                return back()
                    ->withErrors(['idbarang' => 'Total pengadaan melebihi batas nilai yang didukung.'])
                    ->withInput();
            }

            DB::update(
                'UPDATE pengadaan
                 SET status = ?, vendor_idvendor = ?,
                     subtotal_nilai = ?, ppn = ?, total_nilai = ?
                 WHERE idpengadaan = ?',
                [
                    $validated['status'],
                    $validated['vendor_idvendor'],
                    $subtotal,
                    $ppn,
                    $total,
                    $id,
                ]
            );

            DB::delete(
                'DELETE FROM detail_pengadaan
                 WHERE idpengadaan = ?',
                [$id]
            );

            foreach ($hasilDetail['baris'] as $baris) {
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

            return redirect()
                ->route('pengadaan.index')
                ->with('success', 'Pengadaan berhasil diperbarui.');
        }, 3);
    }

    public function destroy(int $id): RedirectResponse
    {
        return DB::transaction(function () use ($id): RedirectResponse {
            $pengadaan = DB::selectOne(
                'SELECT idpengadaan
                 FROM pengadaan
                 WHERE idpengadaan = ? FOR UPDATE',
                [$id]
            );

            if (! $pengadaan) {
                abort(404);
            }

            if ($this->sudahMemilikiPenerimaan($id)) {
                return redirect()
                    ->route('pengadaan.index')
                    ->with('error', 'Pengadaan tidak dapat dihapus karena sudah memiliki penerimaan.');
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

            return redirect()
                ->route('pengadaan.index')
                ->with('success', 'Pengadaan berhasil dihapus.');
        }, 3);
    }

    private function rincianPengadaan(?int $id = null): array
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

    private function aturanValidasi(): array
    {
        return [
            'vendor_idvendor' => 'required|integer',
            'idbarang' => 'required|array|list|min:1',
            'idbarang.*' => 'required|integer|distinct',
            'jumlah' => 'required|array|list|min:1',
            'jumlah.*' => 'required|integer|min:1|max:2147483647',
        ];
    }

    private function siapkanDetailPengadaan(array $validated, ?int $idPengadaan = null): array
    {
        $idBarang = array_map('intval', array_values($validated['idbarang']));
        $jumlah = array_map('intval', array_values($validated['jumlah']));

        if (count($idBarang) !== count($jumlah)) {
            return ['baris' => [], 'subtotal' => 0, 'error' => 'Rincian barang tidak lengkap.'];
        }

        $barang = $idPengadaan === null
            ? $this->daftarBarangAktif()
            : $this->daftarBarangUntukEdit($idPengadaan);
        $hargaPerBarang = array_column($barang, 'harga', 'idbarang');

        $baris = [];
        $subtotal = 0;

        foreach ($idBarang as $index => $barangId) {
            if (! isset($hargaPerBarang[$barangId])) {
                return ['baris' => [], 'subtotal' => 0, 'error' => 'Barang tidak ditemukan atau tidak aktif.'];
            }

            $hargaSatuan = (int) $hargaPerBarang[$barangId];
            $subTotalBaris = $jumlah[$index] * $hargaSatuan;

            if ($subTotalBaris < 0 || $subTotalBaris > 2147483647 || $subtotal > 2147483647 - $subTotalBaris) {
                return ['baris' => [], 'subtotal' => 0, 'error' => 'Subtotal pengadaan melebihi batas nilai yang didukung.'];
            }

            $subtotal += $subTotalBaris;
            $baris[] = [
                'idbarang' => $barangId,
                'jumlah' => $jumlah[$index],
                'harga_satuan' => $hargaSatuan,
                'sub_total' => $subTotalBaris,
            ];
        }

        return ['baris' => $baris, 'subtotal' => $subtotal, 'error' => null];
    }

    private function daftarVendorAktif(?int $vendorSaatIni = null): array
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

    private function daftarBarangAktif(): array
    {
        return DB::select(
            'SELECT idbarang, nama, harga, status
             FROM v_master_barang
             WHERE status = ?
             ORDER BY nama',
            [1]
        );
    }

    private function daftarBarangUntukEdit(int $idPengadaan): array
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

    private function validasiRelasi(array $validated, ?int $vendorSaatIni = null): array
    {
        $errors = [];

        if ($vendorSaatIni === null) {
            $vendor = DB::selectOne(
                'SELECT idvendor
                 FROM v_master_vendor
                 WHERE idvendor = ?
                 AND status = ?',
                [$validated['vendor_idvendor'], 'A']
            );
        } else {
            $vendor = DB::selectOne(
                'SELECT idvendor
                 FROM v_master_vendor
                 WHERE idvendor = ?
                 AND (status = ? OR idvendor = ?)',
                [$validated['vendor_idvendor'], 'A', $vendorSaatIni]
            );
        }

        if (! $vendor) {
            $errors['vendor_idvendor'] = 'Vendor tidak ditemukan atau tidak aktif.';
        }

        return $errors;
    }

    private function sudahMemilikiPenerimaan(int $idPengadaan): bool
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

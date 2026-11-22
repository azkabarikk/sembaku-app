<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BarangController extends Controller
{
    // Membaca barang dari v_master_barang dan memakai fn_harga_jual untuk menghitung harga jual
    // Persentase hanya diambil jika tepat satu margin aktif, lalu hasilnya dikirim ke daftar barang
    public function index(): View
    {
        $marginAktif = DB::select(
            'SELECT persen FROM margin_penjualan WHERE status = ? ORDER BY idmargin_penjualan LIMIT 2',
            [1]
        );
        $persenMargin = count($marginAktif) === 1 ? $marginAktif[0]->persen : null;

        $daftarBarang = DB::select(
            'SELECT idbarang, nama, jenis, nama_satuan, harga, status, fn_harga_jual(harga, ?) AS harga_jual
             FROM v_master_barang
             ORDER BY idbarang',
            [$persenMargin]
        );

        return view('barang.index', [
            'daftarBarang' => $daftarBarang,
            'jumlahMarginAktif' => count($marginAktif),
        ]);
    }

    // Mengambil satuan aktif dari v_master_satuan sebagai pilihan pada formulir tambah barang
    public function create(): View
    {
        $daftarSatuan = DB::select(
            'SELECT idsatuan, nama_satuan
             FROM v_master_satuan
             WHERE status = ?
             ORDER BY nama_satuan',
            [1]
        );

        return view('barang.tambah', [
            'daftarSatuan' => $daftarSatuan,
        ]);
    }

    // Memvalidasi data barang dan harga awal, lalu memeriksa apakah satuan yang dipilih masih aktif
    // Jika sesuai, barang disimpan dan pengguna kembali ke daftar barang
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:45',
            'jenis' => 'required|in:R,S',
            'idsatuan' => 'required|integer',
            'harga' => 'required|integer|min:0|max:2147483647',
            'status' => 'required|in:0,1',
        ]);

        $satuanAktif = DB::selectOne(
            'SELECT idsatuan
             FROM v_master_satuan
             WHERE idsatuan = ?
             AND status = ?',
            [$validated['idsatuan'], 1]
        );

        if (! $satuanAktif) {
            return back()
                ->withErrors([
                    'idsatuan' => 'Satuan tidak ditemukan atau tidak aktif.',
                ])
                ->withInput();
        }

        DB::insert(
            'INSERT INTO barang (nama, jenis, idsatuan, harga, status)
             VALUES (?, ?, ?, ?, ?)',
            [
                $validated['nama'],
                $validated['jenis'],
                $validated['idsatuan'],
                $validated['harga'],
                $validated['status'],
            ]
        );

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    // Mencari barang berdasarkan ID dan mengembalikan 404 jika barang tidak ditemukan
    // Formulir menampilkan data barang, pilihan satuan, dan penanda harga terkunci berdasarkan riwayat penerimaan
    public function edit(int $id): View
    {
        $barang = DB::selectOne(
            'SELECT idbarang, nama, jenis, idsatuan, harga, status
             FROM v_master_barang
             WHERE idbarang = ?',
            [$id]
        );

        if (! $barang) {
            abort(404);
        }

        $daftarSatuan = DB::select(
            'SELECT idsatuan, nama_satuan, status
             FROM v_master_satuan
             ORDER BY nama_satuan'
        );

        return view('barang.edit', [
            'barang' => $barang,
            'hargaTerkunci' => $this->memilikiPenerimaan($id),
            'daftarSatuan' => $daftarSatuan,
        ]);
    }

    // Memvalidasi input dan mengunci barang selama perubahan disimpan dalam satu transaksi
    // Harga lama dipertahankan jika barang sudah diterima, sedangkan penggantian satuan harus memakai satuan aktif
    // Setelah data tersimpan, pengguna kembali ke daftar barang
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:45',
            'jenis' => 'required|in:R,S',
            'idsatuan' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        return DB::transaction(function () use ($request, $validated, $id): RedirectResponse {
            $barang = DB::selectOne(
                'SELECT idsatuan, harga FROM barang WHERE idbarang = ? FOR UPDATE',
                [$id]
            );
            abort_if(! $barang, 404);

            $harga = $this->memilikiPenerimaan($id)
                ? $barang->harga
                : $request->validate(['harga' => 'required|integer|min:0|max:2147483647'])['harga'];

            $satuanAktif = DB::selectOne(
                'SELECT idsatuan FROM v_master_satuan
                 WHERE idsatuan = ? AND (status = ? OR idsatuan = ?)',
                [$validated['idsatuan'], 1, $barang->idsatuan]
            );

            if (! $satuanAktif) {
                return back()
                    ->withErrors(['idsatuan' => 'Satuan tidak ditemukan atau tidak aktif.'])
                    ->withInput();
            }

            DB::update(
                'UPDATE barang SET nama = ?, jenis = ?, idsatuan = ?, harga = ?, status = ?
                 WHERE idbarang = ?',
                [$validated['nama'], $validated['jenis'], $validated['idsatuan'], $harga, $validated['status'], $id]
            );

            return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
        }, 3);
    }

    // Memeriksa keberadaan barang, lalu mengubah statusnya menjadi nonaktif
    // Pengguna kembali ke daftar barang dengan pesan berhasil
    public function destroy(int $id): RedirectResponse
    {
        $barang = DB::selectOne(
            'SELECT idbarang
             FROM v_master_barang
             WHERE idbarang = ?',
            [$id]
        );

        if (! $barang) {
            abort(404);
        }

        DB::update(
            'UPDATE barang
             SET status = ?
             WHERE idbarang = ?',
            [0, $id]
        );

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dinonaktifkan.');
    }

    // Memeriksa apakah barang tercatat pada penerimaan berstatus K atau A
    // Hasilnya menentukan apakah harga beli barang dikunci saat pengeditan
    private function memilikiPenerimaan(int $id): bool
    {
        return DB::selectOne(
            'SELECT d.iddetail_penerimaan
             FROM detail_penerimaan d JOIN penerimaan p ON p.idpenerimaan = d.idpenerimaan
             WHERE d.barang_idbarang = ? AND p.status IN (?, ?) LIMIT 1',
            [$id, 'K', 'A']
        ) !== null;
    }
}

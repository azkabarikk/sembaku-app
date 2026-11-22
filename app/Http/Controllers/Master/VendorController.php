<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VendorController extends Controller
{
    // Menampilkan nama vendor, badan hukum, dan status dari v_master_vendor menurut urutan ID
    public function index(): View
    {
        $daftarVendor = DB::select(
            'SELECT idvendor, nama_vendor, badan_hukum, status
             FROM v_master_vendor
             ORDER BY idvendor'
        );

        return view('vendor.index', [
            'daftarVendor' => $daftarVendor,
        ]);
    }

    // Menampilkan formulir untuk mengisi nama vendor, badan hukum, dan statusnya
    public function create(): View
    {
        return view('vendor.create');
    }

    // Memvalidasi nama vendor serta pilihan badan hukum dan status sebelum menyimpan vendor baru
    // Setelah tersimpan, pengguna kembali ke daftar vendor
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|in:B,N',
            'status' => 'required|in:A,N',
        ]);

        DB::insert(
            'INSERT INTO vendor (nama_vendor, badan_hukum, status)
             VALUES (?, ?, ?)',
            [
                $validated['nama_vendor'],
                $validated['badan_hukum'],
                $validated['status'],
            ]
        );

        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor berhasil ditambahkan.');
    }

    // Mencari vendor berdasarkan ID untuk mengisi formulir edit atau menampilkan 404 jika tidak ditemukan
    public function edit(int $id): View
    {
        $vendor = DB::selectOne(
            'SELECT idvendor, nama_vendor, badan_hukum, status
             FROM v_master_vendor
             WHERE idvendor = ?',
            [$id]
        );

        if (! $vendor) {
            abort(404);
        }

        return view('vendor.edit', [
            'vendor' => $vendor,
        ]);
    }

    // Memvalidasi nama, badan hukum, dan status serta memastikan vendor terdaftar
    // Perubahan disimpan berdasarkan ID, lalu pengguna kembali ke daftar vendor
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|in:B,N',
            'status' => 'required|in:A,N',
        ]);

        $vendor = DB::selectOne(
            'SELECT idvendor
             FROM v_master_vendor
             WHERE idvendor = ?',
            [$id]
        );

        if (! $vendor) {
            abort(404);
        }

        DB::update(
            'UPDATE vendor
             SET nama_vendor = ?, badan_hukum = ?, status = ?
             WHERE idvendor = ?',
            [
                $validated['nama_vendor'],
                $validated['badan_hukum'],
                $validated['status'],
                $id,
            ]
        );

        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor berhasil diperbarui.');
    }

    // Memastikan vendor terdaftar, lalu mengubah statusnya menjadi N untuk menonaktifkannya
    // Pengguna kembali ke daftar vendor dengan pesan berhasil
    public function destroy(int $id): RedirectResponse
    {
        $vendor = DB::selectOne(
            'SELECT idvendor
             FROM v_master_vendor
             WHERE idvendor = ?',
            [$id]
        );

        if (! $vendor) {
            abort(404);
        }

        DB::update(
            'UPDATE vendor
             SET status = ?
             WHERE idvendor = ?',
            ['N', $id]
        );

        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor berhasil dinonaktifkan.');
    }
}

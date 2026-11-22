<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SatuanController extends Controller
{
    // Membaca nama dan status dari v_master_satuan untuk menampilkan daftar satuan menurut urutan ID
    public function index(): View
    {
        $daftarSatuan = DB::select(
            'SELECT idsatuan, nama_satuan, status
             FROM v_master_satuan
             ORDER BY idsatuan'
        );

        return view('satuan.index', [
            'daftarSatuan' => $daftarSatuan,
        ]);
    }

    // Menampilkan formulir untuk mengisi nama dan status satuan baru
    public function create(): View
    {
        return view('satuan.create');
    }

    // Memvalidasi nama dan status satuan, lalu menyimpannya ke tabel satuan
    // Pengguna kembali ke daftar satuan setelah data tersimpan
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:45',
            'status' => 'required|in:0,1',
        ]);

        DB::insert(
            'INSERT INTO satuan (nama_satuan, status)
             VALUES (?, ?)',
            [
                $validated['nama_satuan'],
                $validated['status'],
            ]
        );

        return redirect()
            ->route('satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    // Mencari satuan berdasarkan ID untuk mengisi formulir edit atau menampilkan 404 jika tidak ditemukan
    public function edit(int $id): View
    {
        $satuan = DB::selectOne(
            'SELECT idsatuan, nama_satuan, status
             FROM v_master_satuan
             WHERE idsatuan = ?',
            [$id]
        );

        if (! $satuan) {
            abort(404);
        }

        return view('satuan.edit', [
            'satuan' => $satuan,
        ]);
    }

    // Memvalidasi nama dan status baru serta memastikan satuan terdaftar
    // Perubahan disimpan berdasarkan ID, lalu pengguna kembali ke daftar satuan
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:45',
            'status' => 'required|in:0,1',
        ]);

        $satuan = DB::selectOne(
            'SELECT idsatuan
             FROM v_master_satuan
             WHERE idsatuan = ?',
            [$id]
        );

        if (! $satuan) {
            abort(404);
        }

        DB::update(
            'UPDATE satuan
             SET nama_satuan = ?, status = ?
             WHERE idsatuan = ?',
            [
                $validated['nama_satuan'],
                $validated['status'],
                $id,
            ]
        );

        return redirect()
            ->route('satuan.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    // Memastikan satuan terdaftar, lalu mengubah statusnya menjadi nonaktif
    // Pengguna kembali ke daftar satuan dengan pesan berhasil
    public function destroy(int $id): RedirectResponse
    {
        $satuan = DB::selectOne(
            'SELECT idsatuan
             FROM v_master_satuan
             WHERE idsatuan = ?',
            [$id]
        );

        if (! $satuan) {
            abort(404);
        }

        DB::update(
            'UPDATE satuan
             SET status = ?
             WHERE idsatuan = ?',
            [0, $id]
        );

        return redirect()
            ->route('satuan.index')
            ->with('success', 'Satuan berhasil dinonaktifkan.');
    }
}

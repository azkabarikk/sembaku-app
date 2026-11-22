<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    // Mengambil nama role dari v_master_role dan menampilkannya menurut urutan ID
    public function index(): View
    {
        $daftarRole = DB::select(
            'SELECT idrole, nama_role
             FROM v_master_role
             ORDER BY idrole'
        );

        return view('role.index', [
            'daftarRole' => $daftarRole,
        ]);
    }

    // Menampilkan formulir untuk mengisi nama role baru
    public function create(): View
    {
        return view('role.create');
    }

    // Memastikan nama role terisi dan panjangnya paling banyak 100 karakter
    // Nama disimpan ke tabel role, lalu pengguna kembali ke daftar role
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:100',
        ]);

        DB::insert(
            'INSERT INTO role (nama_role)
             VALUES (?)',
            [$validated['nama_role']]
        );

        return redirect()
            ->route('role.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    // Mencari role berdasarkan ID untuk mengisi formulir edit atau menampilkan 404 jika tidak ditemukan
    public function edit(int $id): View
    {
        $role = DB::selectOne(
            'SELECT idrole, nama_role
             FROM v_master_role
             WHERE idrole = ?',
            [$id]
        );

        if (! $role) {
            abort(404);
        }

        return view('role.edit', [
            'role' => $role,
        ]);
    }

    // Memvalidasi nama baru dan memastikan ID role terdaftar sebelum memperbarui namanya
    // Setelah tersimpan, pengguna kembali ke daftar role
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:100',
        ]);

        $role = DB::selectOne(
            'SELECT idrole
             FROM v_master_role
             WHERE idrole = ?',
            [$id]
        );

        if (! $role) {
            abort(404);
        }

        DB::update(
            'UPDATE role
             SET nama_role = ?
             WHERE idrole = ?',
            [$validated['nama_role'], $id]
        );

        return redirect()
            ->route('role.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    // Memastikan role ada dan memeriksa apakah masih ada pengguna yang memakainya
    // Role yang masih dipakai dipertahankan dengan pesan alasan, sedangkan role yang bebas dihapus sebelum kembali ke daftar
    public function destroy(int $id): RedirectResponse
    {
        $role = DB::selectOne(
            'SELECT idrole
             FROM v_master_role
             WHERE idrole = ?',
            [$id]
        );

        if (! $role) {
            abort(404);
        }

        $roleDigunakan = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE idrole = ?
             LIMIT 1',
            [$id]
        );

        if ($roleDigunakan) {
            return redirect()
                ->route('role.index')
                ->with('error', 'Role tidak bisa dihapus karena masih digunakan oleh user.');
        }

        DB::delete(
            'DELETE FROM role
             WHERE idrole = ?',
            [$id]
        );

        return redirect()
            ->route('role.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    // Membaca username dan role dari v_master_user untuk ditampilkan menurut urutan ID pengguna
    public function index(): View
    {
        $daftarUser = DB::select(
            'SELECT iduser, username, idrole, nama_role
             FROM v_master_user
             ORDER BY iduser'
        );

        return view('user.index', [
            'daftarUser' => $daftarUser,
        ]);
    }

    // Mengambil daftar role dari v_master_role menurut nama untuk pilihan pada formulir tambah pengguna
    public function create(): View
    {
        $daftarRole = DB::select(
            'SELECT idrole, nama_role
             FROM v_master_role
             ORDER BY nama_role'
        );

        return view('user.create', [
            'daftarRole' => $daftarRole,
        ]);
    }

    // Memvalidasi isian dan konfirmasi kata sandi, lalu memastikan username tersedia dan role terdaftar
    // Kata sandi disimpan dalam bentuk hash sebelum pengguna diarahkan ke daftar akun
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:45',
            'password' => 'required|string|min:8|max:72|confirmed',
            'idrole' => 'required|integer',
        ]);

        $usernameTerpakai = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE username = ?
             LIMIT 1',
            [$validated['username']]
        );

        if ($usernameTerpakai) {
            return back()
                ->withErrors([
                    'username' => 'Username sudah digunakan.',
                ])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $role = DB::selectOne(
            'SELECT idrole
             FROM v_master_role
             WHERE idrole = ?',
            [$validated['idrole']]
        );

        if (! $role) {
            return back()
                ->withErrors([
                    'idrole' => 'Role tidak ditemukan.',
                ])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        DB::insert(
            'INSERT INTO `user` (username, password, idrole)
             VALUES (?, ?, ?)',
            [
                $validated['username'],
                Hash::make($validated['password']),
                $validated['idrole'],
            ]
        );

        return redirect()
            ->route('user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    // Mencari akun berdasarkan ID dan mengembalikan 404 jika akun tidak ditemukan
    // Formulir edit menampilkan username, role saat ini, dan pilihan role yang tersedia
    public function edit(int $id): View
    {
        $user = DB::selectOne(
            'SELECT iduser, username, idrole, nama_role
             FROM v_master_user
             WHERE iduser = ?',
            [$id]
        );

        if (! $user) {
            abort(404);
        }

        $daftarRole = DB::select(
            'SELECT idrole, nama_role
             FROM v_master_role
             ORDER BY nama_role'
        );

        return view('user.edit', [
            'user' => $user,
            'daftarRole' => $daftarRole,
        ]);
    }

    // Memastikan akun ada, username tidak dipakai akun lain, dan role yang dipilih terdaftar
    // Jika kata sandi baru diisi dan konfirmasinya cocok, hash kata sandi ikut diperbarui
    // Setelah perubahan tersimpan, pengguna kembali ke daftar akun
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:45',
            'password' => 'nullable|string|min:8|max:72|confirmed',
            'idrole' => 'required|integer',
        ]);

        $user = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE iduser = ?',
            [$id]
        );

        if (! $user) {
            abort(404);
        }

        $usernameTerpakai = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE username = ?
             AND iduser <> ?
             LIMIT 1',
            [$validated['username'], $id]
        );

        if ($usernameTerpakai) {
            return back()
                ->withErrors([
                    'username' => 'Username sudah digunakan.',
                ])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $role = DB::selectOne(
            'SELECT idrole
             FROM v_master_role
             WHERE idrole = ?',
            [$validated['idrole']]
        );

        if (! $role) {
            return back()
                ->withErrors([
                    'idrole' => 'Role tidak ditemukan.',
                ])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        if (! empty($validated['password'])) {
            DB::update(
                'UPDATE `user`
                 SET username = ?, password = ?, idrole = ?
                 WHERE iduser = ?',
                [
                    $validated['username'],
                    Hash::make($validated['password']),
                    $validated['idrole'],
                    $id,
                ]
            );
        } else {
            DB::update(
                'UPDATE `user`
                 SET username = ?, idrole = ?
                 WHERE iduser = ?',
                [
                    $validated['username'],
                    $validated['idrole'],
                    $id,
                ]
            );
        }

        return redirect()
            ->route('user.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    // Mencari akun berdasarkan ID, lalu mencoba menghapusnya
    // Pengguna kembali ke daftar akun dengan pesan berhasil atau pemberitahuan jika akun masih terkait transaksi
    public function destroy(int $id): RedirectResponse
    {
        $user = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE iduser = ?',
            [$id]
        );

        if (! $user) {
            abort(404);
        }

        try {
            DB::delete(
                'DELETE FROM `user`
                 WHERE iduser = ?',
                [$id]
            );
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return redirect()
                    ->route('user.index')
                    ->with('error', 'User tidak bisa dihapus karena masih terkait dengan data transaksi.');
            }

            throw $exception;
        }

        return redirect()
            ->route('user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
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

    public function create(): View
    {
        return view('role.create');
    }

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

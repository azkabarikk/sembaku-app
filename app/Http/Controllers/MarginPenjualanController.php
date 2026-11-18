<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarginPenjualanController extends Controller
{
    public function index(): View
    {
        $daftarMargin = DB::select(
            'SELECT idmargin_penjualan, created_at, persen, status,
                    iduser, username, updated_at
             FROM v_master_margin_penjualan
             ORDER BY idmargin_penjualan ASC'
        );

        return view('margin_penjualan.index', [
            'daftarMargin' => $daftarMargin,
        ]);
    }

    public function create(): View
    {
        $daftarUser = DB::select(
            'SELECT iduser, username
             FROM v_master_user
             ORDER BY username'
        );

        return view('margin_penjualan.create', [
            'daftarUser' => $daftarUser,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
            'iduser' => 'required|integer',
        ]);

        $user = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE iduser = ?',
            [$validated['iduser']]
        );

        if (! $user) {
            return back()
                ->withErrors([
                    'iduser' => 'User tidak ditemukan.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated): void {
            DB::insert(
                'INSERT INTO margin_penjualan (persen, status, iduser)
                 VALUES (?, 0, ?)',
                [
                    $validated['persen'],
                    $validated['iduser'],
                ]
            );

            if ((int) $validated['status'] === 1) {
                $idMargin = (int) DB::getPdo()->lastInsertId();

                DB::statement('CALL sp_set_margin_aktif(?)', [$idMargin]);
            }
        });

        return redirect()
            ->route('margin-penjualan.index')
            ->with('success', 'Margin penjualan berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $margin = DB::selectOne(
            'SELECT idmargin_penjualan, persen, status, iduser
             FROM v_master_margin_penjualan
             WHERE idmargin_penjualan = ?',
            [$id]
        );

        if (! $margin) {
            abort(404);
        }

        $daftarUser = DB::select(
            'SELECT iduser, username
             FROM v_master_user
             ORDER BY username'
        );

        return view('margin_penjualan.edit', [
            'margin' => $margin,
            'daftarUser' => $daftarUser,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
            'iduser' => 'required|integer',
        ]);

        $margin = DB::selectOne(
            'SELECT idmargin_penjualan
             FROM v_master_margin_penjualan
             WHERE idmargin_penjualan = ?',
            [$id]
        );

        if (! $margin) {
            abort(404);
        }

        $user = DB::selectOne(
            'SELECT iduser
             FROM v_master_user
             WHERE iduser = ?',
            [$validated['iduser']]
        );

        if (! $user) {
            return back()
                ->withErrors([
                    'iduser' => 'User tidak ditemukan.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $id): void {
            DB::update(
                'UPDATE margin_penjualan
                 SET persen = ?, status = 0, iduser = ?
                 WHERE idmargin_penjualan = ?',
                [
                    $validated['persen'],
                    $validated['iduser'],
                    $id,
                ]
            );

            if ((int) $validated['status'] === 1) {
                DB::statement('CALL sp_set_margin_aktif(?)', [$id]);
            }
        });

        return redirect()
            ->route('margin-penjualan.index')
            ->with('success', 'Margin penjualan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $margin = DB::selectOne(
            'SELECT idmargin_penjualan
             FROM v_master_margin_penjualan
             WHERE idmargin_penjualan = ?',
            [$id]
        );

        if (! $margin) {
            abort(404);
        }

        DB::update(
            'UPDATE margin_penjualan
             SET status = ?
             WHERE idmargin_penjualan = ?',
            [0, $id]
        );

        return redirect()
            ->route('margin-penjualan.index')
            ->with('success', 'Margin penjualan berhasil dinonaktifkan.');
    }
}

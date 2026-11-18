<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SatuanController extends Controller
{
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

    public function create(): View
    {
        return view('satuan.create');
    }

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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VendorController extends Controller
{
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

    public function create(): View
    {
        return view('vendor.create');
    }

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

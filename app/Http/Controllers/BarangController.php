<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BarangController extends Controller
{
    public function index(): View
    {
        $daftarBarang = DB::select(
            'SELECT idbarang, nama, jenis, nama_satuan, harga, status
             FROM v_master_barang
             ORDER BY idbarang'
        );

        return view('barang.index', [
            'daftarBarang' => $daftarBarang,
        ]);
    }

    public function create()
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:45',
            'jenis' => 'required|in:R,S',
            'idsatuan' => 'required|integer',
            'harga' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $satuan = DB::selectOne(
            'SELECT idsatuan
             FROM satuan
             WHERE idsatuan = ?
             AND status = ?',
            [$validated['idsatuan'], 1]
        );

        if (! $satuan) {
            return back()
                ->withErrors([
                    'idsatuan' => 'Satuan tidak ditemukan atau tidak aktif.',
                ])
                ->withInput();
        }

        DB::insert(
            'INSERT INTO barang
                (nama, jenis, idsatuan, harga, status)
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

    public function edit($id)
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
            'SELECT idsatuan, nama_satuan
             FROM v_master_satuan
         WHERE status = ?
         ORDER BY nama_satuan',
            [1]
        );

        return view('barang.edit', [
            'barang' => $barang,
            'daftarSatuan' => $daftarSatuan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:45',
            'jenis' => 'required|in:R,S',
            'idsatuan' => 'required|integer',
            'harga' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $barang = DB::selectOne(
            'SELECT idbarang
         FROM barang
         WHERE idbarang = ?',
            [$id]
        );

        if (! $barang) {
            abort(404);
        }

        $satuan = DB::selectOne(
            'SELECT idsatuan
         FROM satuan
         WHERE idsatuan = ?
         AND status = ?',
            [$validated['idsatuan'], 1]
        );

        if (! $satuan) {
            return back()
                ->withErrors([
                    'idsatuan' => 'Satuan tidak ditemukan atau tidak aktif.',
                ])
                ->withInput();
        }

        DB::update(
            'UPDATE barang
         SET nama = ?,
             jenis = ?,
             idsatuan = ?,
             harga = ?,
             status = ?
         WHERE idbarang = ?',
            [
                $validated['nama'],
                $validated['jenis'],
                $validated['idsatuan'],
                $validated['harga'],
                $validated['status'],
                $id,
            ]
        );

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $barang = DB::selectOne(
            'SELECT idbarang
         FROM barang
         WHERE idbarang = ?',
            [$id]
        );

        if (! $barang) {
            abort(404);
        }

        DB::update(
            'UPDATE barang
         SET status = 0
         WHERE idbarang = ?',
            [$id]
        );

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dinonaktifkan.');
    }
}

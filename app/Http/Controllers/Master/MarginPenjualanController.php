<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
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
        return view('margin_penjualan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
        ]);

        $idUser = (int) $request->user()->getAuthIdentifier();

        DB::transaction(function () use ($validated, $idUser): void {
            $this->kunciDaftarMargin();

            DB::insert(
                'INSERT INTO margin_penjualan (persen, status, iduser)
                 VALUES (?, 0, ?)',
                [
                    $validated['persen'],
                    $idUser,
                ]
            );

            if ((int) $validated['status'] === 1) {
                $idMargin = (int) DB::getPdo()->lastInsertId();

                $this->aktifkanMargin($idMargin);
            }
        }, 3);

        return redirect()
            ->route('margin-penjualan.index')
            ->with('success', 'Margin penjualan berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $margin = DB::selectOne(
            'SELECT idmargin_penjualan, persen, status
             FROM v_master_margin_penjualan
             WHERE idmargin_penjualan = ?',
            [$id]
        );

        if (! $margin) {
            abort(404);
        }

        return view('margin_penjualan.edit', [
            'margin' => $margin,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($validated, $id): void {
            $this->kunciDaftarMargin();
            $this->pastikanMarginAda($id);

            DB::update(
                'UPDATE margin_penjualan
                 SET persen = ?, status = 0
                 WHERE idmargin_penjualan = ?',
                [
                    $validated['persen'],
                    $id,
                ]
            );

            if ((int) $validated['status'] === 1) {
                $this->aktifkanMargin($id);
            }
        }, 3);

        return redirect()
            ->route('margin-penjualan.index')
            ->with('success', 'Margin penjualan berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $this->kunciDaftarMargin();
            $this->pastikanMarginAda($id);
            DB::update('UPDATE margin_penjualan SET status = ? WHERE idmargin_penjualan = ?', [0, $id]);
        }, 3);

        return redirect()
            ->route('margin-penjualan.index')
            ->with('success', 'Margin penjualan berhasil dinonaktifkan.');
    }

    private function aktifkanMargin(int $id): void
    {
        $this->pastikanMarginAda($id);
        DB::statement('CALL sp_set_margin_aktif(?)', [$id]);
    }

    private function pastikanMarginAda(int $id): void
    {
        $margin = DB::selectOne('SELECT idmargin_penjualan FROM margin_penjualan WHERE idmargin_penjualan = ?', [$id]);
        abort_if(! $margin, 404);
    }

    private function kunciDaftarMargin(): void
    {
        DB::select('SELECT idmargin_penjualan FROM margin_penjualan ORDER BY idmargin_penjualan FOR UPDATE');
    }
}

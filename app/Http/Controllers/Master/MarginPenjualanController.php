<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarginPenjualanController extends Controller
{
    // Mengambil persentase, status, dan data pengguna dari v_master_margin_penjualan
    // Daftar margin ditampilkan dari ID terkecil
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

    // Menampilkan formulir untuk mengisi persentase dan status margin baru
    public function create(): View
    {
        return view('margin_penjualan.create');
    }

    // Memvalidasi persentase dan status, lalu mengambil ID pengguna yang sedang masuk sebagai pembuat
    // Dalam satu transaksi, daftar margin dikunci dan margin baru disimpan dengan status nonaktif
    // Jika dipilih aktif, prosedur margin mengaktifkannya sebelum pengguna kembali ke daftar
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

    // Mengambil persentase dan status margin berdasarkan ID untuk mengisi formulir edit
    // Jika ID tidak ditemukan, pengguna mendapat halaman 404
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

    // Memvalidasi persentase dan status, lalu mengunci daftar margin dan memastikan ID terdaftar
    // Dalam transaksi yang sama, persentase disimpan dengan status nonaktif dan margin diaktifkan jika diminta
    // Pengguna kembali ke daftar setelah perubahan tersimpan
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

    // Mengunci daftar margin dan memeriksa ID sebelum mengubah statusnya menjadi nonaktif
    // Setelah transaksi selesai, pengguna kembali ke daftar margin
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

    // Memastikan ID margin terdaftar, lalu memanggil sp_set_margin_aktif
    // Prosedur menonaktifkan margin aktif sebelumnya dan mengaktifkan margin yang dipilih
    private function aktifkanMargin(int $id): void
    {
        $this->pastikanMarginAda($id);
        DB::statement('CALL sp_set_margin_aktif(?)', [$id]);
    }

    // Mencari ID pada tabel margin penjualan dan menghentikan proses dengan respons 404 jika tidak ditemukan
    private function pastikanMarginAda(int $id): void
    {
        $margin = DB::selectOne('SELECT idmargin_penjualan FROM margin_penjualan WHERE idmargin_penjualan = ?', [$id]);
        abort_if(! $margin, 404);
    }

    // Mengunci baris margin menurut urutan ID agar transaksi lain menunggu sebelum mengubah baris yang sama
    private function kunciDaftarMargin(): void
    {
        DB::select('SELECT idmargin_penjualan FROM margin_penjualan ORDER BY idmargin_penjualan FOR UPDATE');
    }
}

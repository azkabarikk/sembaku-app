<?php

namespace App\Http\Controllers;

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
             ORDER BY created_at DESC, idmargin_penjualan DESC'
        );

        return view('margin_penjualan.index', [
            'daftarMargin' => $daftarMargin,
        ]);
    }
}

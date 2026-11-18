<?php

namespace App\Http\Controllers;

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
}

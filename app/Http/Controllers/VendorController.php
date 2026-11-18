<?php

namespace App\Http\Controllers;

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
}

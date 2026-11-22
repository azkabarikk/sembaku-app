<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReturController extends Controller
{
    // Membuka halaman utama retur
    public function index(): View
    {
        return view('retur.index');
    }
}

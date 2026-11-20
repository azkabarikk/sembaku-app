<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(): View
    {
        return view('penjualan.index');
    }
}

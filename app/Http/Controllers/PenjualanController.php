<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(): View
    {
        return view('penjualan.index');
    }
}

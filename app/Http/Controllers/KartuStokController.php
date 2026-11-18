<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class KartuStokController extends Controller
{
    public function index(): View
    {
        return view('kartu_stok.index');
    }
}

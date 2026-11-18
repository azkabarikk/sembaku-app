<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PengadaanController extends Controller
{
    public function index(): View
    {
        return view('pengadaan.index');
    }
}

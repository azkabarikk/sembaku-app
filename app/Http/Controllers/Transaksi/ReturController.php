<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReturController extends Controller
{
    public function index(): View
    {
        return view('retur.index');
    }
}

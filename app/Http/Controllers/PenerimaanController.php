<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PenerimaanController extends Controller
{
    public function index(): View
    {
        return view('penerimaan.index');
    }
}

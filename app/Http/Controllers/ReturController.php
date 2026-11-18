<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReturController extends Controller
{
    public function index(): View
    {
        return view('retur.index');
    }
}

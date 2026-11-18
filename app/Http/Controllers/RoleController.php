<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $daftarRole = DB::select(
            'SELECT idrole, nama_role
             FROM v_master_role
             ORDER BY idrole'
        );

        return view('role.index', [
            'daftarRole' => $daftarRole,
        ]);
    }
}

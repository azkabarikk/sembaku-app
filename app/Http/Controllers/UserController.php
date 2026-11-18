<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $daftarUser = DB::select(
            'SELECT iduser, username, idrole, nama_role
             FROM v_master_user
             ORDER BY iduser'
        );

        return view('user.index', [
            'daftarUser' => $daftarUser,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Menampilkan formulir masuk dengan username dan kata sandi
    public function create(): View
    {
        return view('auth.login');
    }

    // Memvalidasi username dan kata sandi, lalu membatasi percobaan masuk berdasarkan username dan alamat IP
    // Jika autentikasi berhasil, penghitung percobaan dihapus dan ID sesi diperbarui
    // Pengguna diarahkan ke halaman yang semula dituju atau ke stok barang
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:45',
            'password' => 'required|string|max:255',
        ]);
        $key = 'login:'.Str::lower($credentials['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['username' => 'Terlalu banyak percobaan masuk. Coba lagi sebentar.']);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages(['username' => 'Username atau password tidak sesuai.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('stok-barang.index'));
    }

    // Mengeluarkan pengguna, mengakhiri sesi, dan membuat token CSRF baru sebelum kembali ke halaman masuk
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

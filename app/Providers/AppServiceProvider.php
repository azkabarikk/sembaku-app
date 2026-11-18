<?php

namespace App\Providers;

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('sembaku-sql', static fn (): RawSqlUserProvider => new RawSqlUserProvider);

        $akses = [
            'administrasi' => ['Admin'],
            'pengadaan.baca' => ['Admin', 'Petugas Gudang', 'Staf Pengadaan', 'Supervisor'],
            'pengadaan.kelola' => ['Admin', 'Staf Pengadaan', 'Supervisor'],
            'penerimaan.baca' => ['Admin', 'Petugas Gudang', 'Staf Pengadaan', 'Supervisor'],
            'penerimaan.draf' => ['Admin', 'Petugas Gudang', 'Supervisor'],
            'penerimaan.konfirmasi' => ['Admin', 'Supervisor'],
            'kartu-stok.baca' => ['Admin', 'Petugas Gudang', 'Staf Pengadaan', 'Supervisor'],
            'stok-barang.baca' => ['Admin', 'Kasir', 'Petugas Gudang', 'Staf Pengadaan', 'Supervisor'],
        ];

        foreach ($akses as $izin => $role) {
            Gate::define($izin, static fn (GenericUser $user): bool => in_array($user->nama_role, $role, true));
        }

        Gate::define('penerimaan.ubah-draf', static fn (GenericUser $user, object $penerimaan): bool => $penerimaan->status === 'D' && (
            in_array($user->nama_role, ['Admin', 'Supervisor'], true)
            || ($user->nama_role === 'Petugas Gudang' && (int) $user->id === (int) $penerimaan->iduser)
        )
        );
    }
}

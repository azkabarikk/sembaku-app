<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Master\BarangController;
use App\Http\Controllers\Master\MarginPenjualanController;
use App\Http\Controllers\Master\RoleController;
use App\Http\Controllers\Master\SatuanController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\VendorController;
use App\Http\Controllers\Transaksi\KartuStokController;
use App\Http\Controllers\Transaksi\Penerimaan\PenerimaanController;
use App\Http\Controllers\Transaksi\Pengadaan\PengadaanController;
use App\Http\Controllers\Transaksi\PenjualanController;
use App\Http\Controllers\Transaksi\ReturController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/pengadaan', [PengadaanController::class, 'index'])
        ->middleware('can:pengadaan.baca')
        ->name('pengadaan.index');

    Route::get('/pengadaan/create', [PengadaanController::class, 'create'])
        ->middleware('can:pengadaan.kelola')
        ->name('pengadaan.create');

    Route::post('/pengadaan', [PengadaanController::class, 'store'])
        ->middleware('can:pengadaan.kelola')
        ->name('pengadaan.store');

    Route::get('/pengadaan/{id}/edit', [PengadaanController::class, 'edit'])
        ->middleware('can:pengadaan.kelola')
        ->name('pengadaan.edit');

    Route::put('/pengadaan/{id}', [PengadaanController::class, 'update'])
        ->middleware('can:pengadaan.kelola')
        ->name('pengadaan.update');

    Route::delete('/pengadaan/{id}', [PengadaanController::class, 'destroy'])
        ->middleware('can:pengadaan.kelola')
        ->name('pengadaan.destroy');

    Route::get('/pengadaan/{id}', [PengadaanController::class, 'show'])
        ->whereNumber('id')
        ->middleware('can:pengadaan.baca')
        ->name('pengadaan.show');

    Route::get('/penerimaan', [PenerimaanController::class, 'index'])
        ->middleware('can:penerimaan.baca')
        ->name('penerimaan.index');

    Route::get('/penerimaan/create', [PenerimaanController::class, 'pilihPengadaan'])
        ->middleware('can:penerimaan.draf')
        ->name('penerimaan.pilih-pengadaan');

    Route::get('/pengadaan/{idpengadaan}/penerimaan/create', [PenerimaanController::class, 'create'])
        ->middleware('can:penerimaan.draf')
        ->name('penerimaan.create');

    Route::post('/pengadaan/{idpengadaan}/penerimaan', [PenerimaanController::class, 'store'])
        ->middleware('can:penerimaan.draf')
        ->name('penerimaan.store');

    Route::get('/penerimaan/{id}/edit', [PenerimaanController::class, 'edit'])
        ->middleware('can:penerimaan.draf')
        ->name('penerimaan.edit');

    Route::put('/penerimaan/{id}', [PenerimaanController::class, 'update'])
        ->middleware('can:penerimaan.draf')
        ->name('penerimaan.update');

    Route::delete('/penerimaan/{id}', [PenerimaanController::class, 'destroy'])
        ->middleware('can:penerimaan.draf')
        ->name('penerimaan.destroy');

    Route::get('/penerimaan/{id}', [PenerimaanController::class, 'show'])
        ->whereNumber('id')
        ->middleware('can:penerimaan.baca')
        ->name('penerimaan.show');

    Route::get('/kartu-stok', [KartuStokController::class, 'index'])
        ->middleware('can:kartu-stok.baca')
        ->name('kartu-stok.index');

    Route::get('/stok-barang', [KartuStokController::class, 'ringkasan'])
        ->middleware('can:stok-barang.baca')
        ->name('stok-barang.index');

    Route::get('/penjualan', [PenjualanController::class, 'index'])
        ->middleware('can:penjualan.kelola')
        ->block(60, 15)
        ->name('penjualan.index');

    Route::post('/penjualan', [PenjualanController::class, 'store'])
        ->middleware('can:penjualan.kelola')
        ->block(60, 15)
        ->name('penjualan.store');

    Route::get('/retur', [ReturController::class, 'index'])
        ->middleware('can:retur.baca')
        ->name('retur.index');

    Route::get('/retur/sumber', [ReturController::class, 'sumber'])
        ->middleware('can:retur.baca')
        ->name('retur.sumber');

    Route::get('/retur/sumber/{id}', [ReturController::class, 'rincianSumber'])
        ->whereNumber('id')
        ->middleware('can:retur.baca')
        ->name('retur.sumber.show');

    Route::get('/retur/create/{id}', [ReturController::class, 'create'])
        ->whereNumber('id')
        ->middleware('can:retur.buat')
        ->name('retur.create');

    Route::get('/retur/{id}', [ReturController::class, 'show'])
        ->whereNumber('id')
        ->middleware('can:retur.baca')
        ->name('retur.show');

    Route::post('/retur', [ReturController::class, 'store'])
        ->middleware('can:retur.buat')
        ->name('retur.store');
});

Route::middleware(['auth', 'can:administrasi'])->group(function (): void {
    Route::get('/barang', [BarangController::class, 'index'])
        ->name('barang.index');

    Route::get('/satuan', [SatuanController::class, 'index'])
        ->name('satuan.index');

    Route::get('/satuan/create', [SatuanController::class, 'create'])
        ->name('satuan.create');

    Route::post('/satuan', [SatuanController::class, 'store'])
        ->name('satuan.store');

    Route::get('/satuan/{id}/edit', [SatuanController::class, 'edit'])
        ->name('satuan.edit');

    Route::put('/satuan/{id}', [SatuanController::class, 'update'])
        ->name('satuan.update');

    Route::delete('/satuan/{id}', [SatuanController::class, 'destroy'])
        ->name('satuan.destroy');

    Route::get('/vendor', [VendorController::class, 'index'])
        ->name('vendor.index');

    Route::get('/vendor/create', [VendorController::class, 'create'])
        ->name('vendor.create');

    Route::post('/vendor', [VendorController::class, 'store'])
        ->name('vendor.store');

    Route::get('/vendor/{id}/edit', [VendorController::class, 'edit'])
        ->name('vendor.edit');

    Route::put('/vendor/{id}', [VendorController::class, 'update'])
        ->name('vendor.update');

    Route::delete('/vendor/{id}', [VendorController::class, 'destroy'])
        ->name('vendor.destroy');

    Route::get('/role', [RoleController::class, 'index'])
        ->name('role.index');

    Route::get('/role/create', [RoleController::class, 'create'])
        ->name('role.create');

    Route::post('/role', [RoleController::class, 'store'])
        ->name('role.store');

    Route::get('/role/{id}/edit', [RoleController::class, 'edit'])
        ->name('role.edit');

    Route::put('/role/{id}', [RoleController::class, 'update'])
        ->name('role.update');

    Route::delete('/role/{id}', [RoleController::class, 'destroy'])
        ->name('role.destroy');

    Route::get('/user', [UserController::class, 'index'])
        ->name('user.index');

    Route::get('/user/create', [UserController::class, 'create'])
        ->name('user.create');

    Route::post('/user', [UserController::class, 'store'])
        ->name('user.store');

    Route::get('/user/{id}/edit', [UserController::class, 'edit'])
        ->name('user.edit');

    Route::put('/user/{id}', [UserController::class, 'update'])
        ->name('user.update');

    Route::delete('/user/{id}', [UserController::class, 'destroy'])
        ->name('user.destroy');

    Route::get('/margin-penjualan', [MarginPenjualanController::class, 'index'])
        ->name('margin-penjualan.index');

    Route::get('/margin-penjualan/create', [MarginPenjualanController::class, 'create'])
        ->name('margin-penjualan.create');

    Route::post('/margin-penjualan', [MarginPenjualanController::class, 'store'])
        ->name('margin-penjualan.store');

    Route::get('/margin-penjualan/{id}/edit', [MarginPenjualanController::class, 'edit'])
        ->name('margin-penjualan.edit');

    Route::put('/margin-penjualan/{id}', [MarginPenjualanController::class, 'update'])
        ->name('margin-penjualan.update');

    Route::delete('/margin-penjualan/{id}', [MarginPenjualanController::class, 'destroy'])
        ->name('margin-penjualan.destroy');

    Route::get('/barang/create', [BarangController::class, 'create'])
        ->name('barang.create');

    Route::post('/barang', [BarangController::class, 'store'])
        ->name('barang.store');

    Route::get('/barang/{id}/edit', [BarangController::class, 'edit'])
        ->name('barang.edit');

    Route::put('/barang/{id}', [BarangController::class, 'update'])
        ->name('barang.update');

    Route::delete('/barang/{id}', [BarangController::class, 'destroy'])
        ->name('barang.destroy');
});

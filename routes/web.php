<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\MarginPenjualanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/barang', [BarangController::class, 'index'])
    ->name('barang.index');

Route::get('/satuan', [SatuanController::class, 'index'])
    ->name('satuan.index');

Route::get('/vendor', [VendorController::class, 'index'])
    ->name('vendor.index');

Route::get('/role', [RoleController::class, 'index'])
    ->name('role.index');

Route::get('/user', [UserController::class, 'index'])
    ->name('user.index');

Route::get('/margin-penjualan', [MarginPenjualanController::class, 'index'])
    ->name('margin-penjualan.index');

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

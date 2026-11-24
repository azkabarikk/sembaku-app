<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenjualanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'token_pembayaran' => ['required', 'string', 'uuid'],
            'uang_diterima' => ['required', 'integer', 'min:0', 'max:'.PHP_INT_MAX],
            'items' => ['required', 'array', 'list', 'min:1', 'max:100'],
            'items.*.idbarang' => ['required', 'integer', 'min:1', 'distinct:strict'],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'token_pembayaran.required' => 'Sesi pembayaran tidak ditemukan. Muat ulang halaman Penjualan.',
            'uang_diterima.required' => 'Masukkan jumlah uang yang diterima dari pelanggan.',
            'uang_diterima.integer' => 'Uang diterima harus dalam rupiah bulat.',
            'token_pembayaran.uuid' => 'Sesi pembayaran tidak valid. Muat ulang halaman Penjualan.',
            'items.required' => 'Keranjang belum berisi barang.',
            'items.min' => 'Keranjang belum berisi barang.',
            'items.*.idbarang.distinct' => 'Barang yang sama tidak boleh dikirim lebih dari sekali.',
            'items.*.jumlah.min' => 'Jumlah barang minimal satu.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenerimaanRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'penerimaan_terakhir' => 'sometimes|integer|min:0',
            'jumlah_terima' => 'required|array',
            'jumlah_terima.*' => 'required|integer|min:0|max:2147483647',
            'harga_satuan_terima' => 'sometimes|array',
            'harga_satuan_terima.*' => 'nullable|integer|min:0|max:2147483647',
        ];
    }
}

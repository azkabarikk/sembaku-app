<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ReturRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('retur.buat');
    }

    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'idpenerimaan' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.iddetail_penerimaan' => 'required|integer|min:1|distinct',
            'items.*.jumlah' => 'required|integer|min:1|max:2147483647',
            'items.*.alasan' => 'required|string|max:200',
        ];
    }
}

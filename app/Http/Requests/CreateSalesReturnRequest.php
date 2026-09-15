<?php

namespace App\Http\Requests;

use App\Enums\RefundMethod;
use App\Enums\SalesReturnCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'exists:sales,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.condition' => ['required', Rule::enum(SalesReturnCondition::class)],
            'refund_method' => ['required', Rule::enum(RefundMethod::class)],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.required' => 'Faktur penjualan wajib dipilih.',
            'sale_id.exists' => 'Faktur penjualan tidak valid.',
            'items.required' => 'Pilih setidaknya satu produk untuk diretur.',
            'items.min' => 'Pilih setidaknya satu produk untuk diretur.',
            'items.*.quantity.min' => 'Jumlah kuantitas retur tidak boleh negatif.',
            'refund_method.required' => 'Metode pengembalian dana wajib dipilih.',
            'reason.required' => 'Alasan retur barang wajib diisi.',
        ];
    }
}

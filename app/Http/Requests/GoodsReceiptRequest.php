<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->value, ['admin', 'manager'], true);
    }

    public function rules(): array
    {
        return [
            'received_date' => ['required', 'date'],
            'delivery_order_number' => ['nullable', 'string', 'max:100'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.received_quantity_base' => ['required', 'integer', 'min:0'],
            'items.*.actual_unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'received_date.required' => 'Tanggal penerimaan fisik wajib diisi.',
            'items.required' => 'Daftar item penerimaan wajib diisi.',
            'items.*.received_quantity_base.min' => 'Kuantitas penerimaan tidak boleh negatif.',
        ];
    }
}

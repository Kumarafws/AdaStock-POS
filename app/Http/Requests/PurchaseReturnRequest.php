<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->value, ['admin', 'manager'], true);
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'goods_receipt_id' => ['nullable', 'exists:goods_receipts,id'],
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier tujuan retur wajib dipilih.',
            'location_id.required' => 'Lokasi asal barang yang diretur wajib dipilih.',
            'return_date.required' => 'Tanggal retur wajib diisi.',
            'reason.required' => 'Alasan retur wajib diisi.',
            'items.required' => 'Minimal 1 item barang yang diretur wajib diisi.',
            'items.*.quantity.min' => 'Jumlah barang yang diretur minimal 1.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->value, ['admin', 'manager'], true);
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'destination_location_id' => ['required', 'exists:locations,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_unit_id' => ['nullable', 'exists:product_units,id'],
            'items.*.ordered_quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier / Pemasok wajib dipilih.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak terdaftar.',
            'destination_location_id.required' => 'Lokasi gudang/toko tujuan wajib ditentukan.',
            'destination_location_id.exists' => 'Lokasi tujuan tidak valid.',
            'order_date.required' => 'Tanggal pemesanan wajib diisi.',
            'items.required' => 'Minimal harus ada 1 item produk dalam Purchase Order.',
            'items.min' => 'Minimal harus ada 1 item produk dalam Purchase Order.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.ordered_quantity.min' => 'Kuantitas pesanan minimal 1.',
            'items.*.unit_cost.min' => 'Harga beli tidak boleh negatif.',
        ];
    }
}

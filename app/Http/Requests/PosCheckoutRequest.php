<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart_items.*.product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
            'cart_items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cart_items.required' => 'Keranjang belanja tidak boleh kosong.',
            'cart_items.min' => 'Pilih minimal satu item produk untuk melanjutkan transaksi.',
            'payments.required' => 'Metode pembayaran wajib ditentukan.',
            'payments.*.amount.min' => 'Nominal pembayaran tidak boleh kurang dari nol.',
        ];
    }
}

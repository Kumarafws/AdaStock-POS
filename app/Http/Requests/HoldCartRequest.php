<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HoldCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'cart_items' => ['required', 'array', 'min:1'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cart_items.required' => 'Keranjang kosong tidak dapat ditahan.',
        ];
    }
}

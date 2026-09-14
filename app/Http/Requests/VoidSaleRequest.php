<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string'],
            'reason' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'PIN Supervisor wajib diisi untuk mengotorisasi pembatalan transaksi.',
            'reason.required' => 'Alasan pembatalan transaksi (void) wajib dipilih atau diisi.',
        ];
    }
}

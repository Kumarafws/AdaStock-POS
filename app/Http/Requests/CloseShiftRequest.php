<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->value, ['cashier', 'manager', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'actual_ending_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_ending_cash.required' => 'Jumlah uang fisik aktual di laci wajib diisi.',
            'actual_ending_cash.min' => 'Jumlah uang fisik aktual tidak boleh negatif.',
        ];
    }
}

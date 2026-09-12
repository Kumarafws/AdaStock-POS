<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role->value, ['cashier', 'manager', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'starting_cash.required' => 'Modal kas awal di laci wajib diisi.',
            'starting_cash.min' => 'Modal kas awal tidak boleh negatif.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'min:4', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'PIN Supervisor wajib diisi.',
            'pin.min' => 'Format PIN Supervisor minimal 4 karakter/digit.',
        ];
    }
}

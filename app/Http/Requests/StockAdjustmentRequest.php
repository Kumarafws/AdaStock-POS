<?php

namespace App\Http\Requests;

use App\Enums\AdjustmentReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admin and manager can perform stock adjustments
        return auth()->check() && in_array(auth()->user()->role->value, ['admin', 'manager'], true);
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', new Enum(AdjustmentReason::class)],
            'action_type' => ['required', Rule::in(['normal', 'to_quarantine', 'disposal'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'Lokasi penyesuaian wajib dipilih.',
            'location_id.exists' => 'Lokasi yang dipilih tidak valid.',
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'type.required' => 'Arah penyesuaian (Masuk/Keluar) wajib ditentukan.',
            'quantity.required' => 'Kuantitas penyesuaian wajib diisi.',
            'quantity.min' => 'Kuantitas penyesuaian minimal 1.',
            'reason.required' => 'Alasan penyesuaian wajib dipilih.',
            'action_type.required' => 'Tipe tindakan penyesuaian wajib ditentukan.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhysicalCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isWarehouse());
    }

    public function rules(): array
    {
        return [
            'counts' => ['required', 'array', 'min:1'],
            'counts.*.item_id' => ['required', 'exists:stock_opname_items,id'],
            'counts.*.physical_qty' => ['required', 'integer', 'min:0'],
            'counts.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}

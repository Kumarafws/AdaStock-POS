<?php

namespace App\Http\Requests;

use App\Enums\LocationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $locationId = $this->route('location')?->id ?? $this->route('location');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations', 'code')->ignore($locationId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                Rule::enum(LocationType::class),
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode lokasi wajib diisi.',
            'code.unique' => 'Kode lokasi sudah terdaftar dalam sistem.',
            'name.required' => 'Nama toko atau gudang wajib diisi.',
            'type.required' => 'Tipe lokasi wajib dipilih.',
            'type.Illuminate\Validation\Rules\Enum' => 'Tipe lokasi yang dipilih tidak valid.',
        ];
    }
}

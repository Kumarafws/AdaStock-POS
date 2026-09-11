<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isManager());
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($productId)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'base_unit_name' => ['required', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'default_selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'reorder_point' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'units' => ['nullable', 'array'],
            'units.*.unit_name' => ['required_with:units', 'string', 'max:30'],
            'units.*.conversion_factor' => ['required_with:units', 'integer', 'min:2'],
            'units.*.barcode' => ['nullable', 'string', 'max:100'],
            'units.*.selling_price' => ['required_with:units', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $productId = $this->route('product')?->id ?? $this->route('product');
            $baseBarcode = $this->input('barcode');
            $units = $this->input('units', []);

            // Check if base barcode clashes with any product_unit barcode
            if ($baseBarcode) {
                $clash = ProductUnit::where('barcode', $baseBarcode)
                    ->when($productId, fn($q) => $q->where('product_id', '!=', $productId))
                    ->exists();

                if ($clash) {
                    $validator->errors()->add('barcode', 'Barcode kemasan dasar sudah digunakan oleh kemasan produk lain.');
                }
            }

            // Check secondary units barcodes
            $unitBarcodes = [];
            $unitNames = [];

            foreach ($units as $index => $unit) {
                $uName = strtolower(trim($unit['unit_name'] ?? ''));
                if (in_array($uName, $unitNames, true)) {
                    $validator->errors()->add("units.{$index}.unit_name", "Nama satuan kemasan '{$unit['unit_name']}' tidak boleh duplikat pada produk yang sama.");
                }
                $unitNames[] = $uName;

                $uBarcode = trim($unit['barcode'] ?? '');
                if (!empty($uBarcode)) {
                    if ($uBarcode === $baseBarcode) {
                        $validator->errors()->add("units.{$index}.barcode", "Barcode kemasan '{$unit['unit_name']}' tidak boleh sama dengan barcode satuan dasar.");
                    }
                    if (in_array($uBarcode, $unitBarcodes, true)) {
                        $validator->errors()->add("units.{$index}.barcode", "Barcode kemasan '{$uBarcode}' duplikat di dalam form.");
                    }
                    $unitBarcodes[] = $uBarcode;

                    // Check across database
                    $clashWithProduct = Product::where('barcode', $uBarcode)
                        ->when($productId, fn($q) => $q->where('id', '!=', $productId))
                        ->exists();

                    $clashWithOtherUnit = ProductUnit::where('barcode', $uBarcode)
                        ->when($productId, fn($q) => $q->where('product_id', '!=', $productId))
                        ->exists();

                    if ($clashWithProduct || $clashWithOtherUnit) {
                        $validator->errors()->add("units.{$index}.barcode", "Barcode kemasan '{$uBarcode}' sudah terdaftar di sistem.");
                    }
                }
            }
        });
    }
}

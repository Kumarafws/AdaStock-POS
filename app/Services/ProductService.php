<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Create product with its optional secondary units in a transaction.
     */
    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $imagePath = null;
            if ($image) {
                $imagePath = $image->store('products', 'public');
            }

            $product = Product::create([
                'sku' => $data['sku'],
                'barcode' => !empty($data['barcode']) ? $data['barcode'] : null,
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'brand_id' => !empty($data['brand_id']) ? $data['brand_id'] : null,
                'base_unit_name' => $data['base_unit_name'] ?? 'Pcs',
                'purchase_price' => $data['purchase_price'] ?? 0,
                'default_selling_price' => $data['default_selling_price'] ?? 0,
                'min_stock' => $data['min_stock'] ?? 10,
                'reorder_point' => $data['reorder_point'] ?? 25,
                'image_path' => $imagePath,
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            if (!empty($data['units']) && is_array($data['units'])) {
                foreach ($data['units'] as $unitData) {
                    if (!empty($unitData['unit_name']) && !empty($unitData['conversion_factor'])) {
                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_name' => $unitData['unit_name'],
                            'conversion_factor' => (int) $unitData['conversion_factor'],
                            'barcode' => !empty($unitData['barcode']) ? $unitData['barcode'] : null,
                            'selling_price' => (float) ($unitData['selling_price'] ?? 0),
                        ]);
                    }
                }
            }

            return $product->load('units', 'category', 'brand');
        });
    }

    /**
     * Update product and synchronize its secondary units in a transaction.
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($product, $data, $image) {
            $imagePath = $product->image_path;

            if ($image) {
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $imagePath = $image->store('products', 'public');
            }

            $product->update([
                'sku' => $data['sku'],
                'barcode' => !empty($data['barcode']) ? $data['barcode'] : null,
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'brand_id' => !empty($data['brand_id']) ? $data['brand_id'] : null,
                'base_unit_name' => $data['base_unit_name'] ?? $product->base_unit_name,
                'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                'default_selling_price' => $data['default_selling_price'] ?? $product->default_selling_price,
                'min_stock' => $data['min_stock'] ?? $product->min_stock,
                'reorder_point' => $data['reorder_point'] ?? $product->reorder_point,
                'image_path' => $imagePath,
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            // Sync units: delete existing and recreate
            $product->units()->delete();

            if (!empty($data['units']) && is_array($data['units'])) {
                foreach ($data['units'] as $unitData) {
                    if (!empty($unitData['unit_name']) && !empty($unitData['conversion_factor'])) {
                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_name' => $unitData['unit_name'],
                            'conversion_factor' => (int) $unitData['conversion_factor'],
                            'barcode' => !empty($unitData['barcode']) ? $unitData['barcode'] : null,
                            'selling_price' => (float) ($unitData['selling_price'] ?? 0),
                        ]);
                    }
                }
            }

            return $product->load('units', 'category', 'brand');
        });
    }

    /**
     * Soft delete product.
     */
    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            return $product->delete();
        });
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Product $product): bool
    {
        $product->is_active = !$product->is_active;
        return $product->save();
    }
}

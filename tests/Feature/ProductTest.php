<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
        $this->category = Category::first();
    }

    public function test_all_roles_can_view_product_catalog(): void
    {
        $this->actingAs($this->admin)->get('/products')->assertStatus(200);
        $this->actingAs($this->manager)->get('/products')->assertStatus(200);
        $this->actingAs($this->cashier)->get('/products')->assertStatus(200);
    }

    public function test_all_roles_can_view_product_detail(): void
    {
        $product = Product::first();

        $this->actingAs($this->cashier)
            ->get("/products/{$product->id}")
            ->assertStatus(200)
            ->assertSee($product->name);
    }

    public function test_cashier_cannot_create_or_modify_products(): void
    {
        // Cannot access create form
        $this->actingAs($this->cashier)
            ->get('/products/create')
            ->assertStatus(403);

        // Cannot store product
        $this->actingAs($this->cashier)
            ->post('/products', [
                'sku' => 'TEST-001',
                'name' => 'Forbidden Product',
                'category_id' => $this->category->id,
                'base_unit_name' => 'Pcs',
                'purchase_price' => 1000,
                'default_selling_price' => 2000,
                'min_stock' => 5,
                'reorder_point' => 10,
            ])
            ->assertStatus(403);
    }

    public function test_manager_can_create_product_with_multi_units(): void
    {
        $response = $this->actingAs($this->manager)->post('/products', [
            'sku' => 'PRD-NEW-001',
            'barcode' => '899123456789',
            'name' => 'Kopi Kapal Api Spesial 65g',
            'category_id' => $this->category->id,
            'base_unit_name' => 'Bungkus',
            'purchase_price' => 4500,
            'default_selling_price' => 5500,
            'min_stock' => 20,
            'reorder_point' => 50,
            'description' => 'Kopi bubuk hitam mantap',
            'units' => [
                [
                    'unit_name' => 'Renceng',
                    'conversion_factor' => 10,
                    'barcode' => '899123456790',
                    'selling_price' => 52000,
                ],
                [
                    'unit_name' => 'Dus',
                    'conversion_factor' => 100,
                    'barcode' => '899123456791',
                    'selling_price' => 500000,
                ],
            ],
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'sku' => 'PRD-NEW-001',
            'name' => 'Kopi Kapal Api Spesial 65g',
            'base_unit_name' => 'Bungkus',
        ]);

        $product = Product::where('sku', 'PRD-NEW-001')->first();
        $this->assertCount(2, $product->units);
        $this->assertDatabaseHas('product_units', [
            'product_id' => $product->id,
            'unit_name' => 'Dus',
            'conversion_factor' => 100,
            'selling_price' => 500000,
        ]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $existing = Product::first();

        $response = $this->actingAs($this->manager)->post('/products', [
            'sku' => $existing->sku, // DUPLICATE
            'name' => 'Clashing SKU Product',
            'category_id' => $this->category->id,
            'base_unit_name' => 'Pcs',
            'purchase_price' => 1000,
            'default_selling_price' => 2000,
            'min_stock' => 5,
            'reorder_point' => 10,
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_barcode_clash_between_base_and_secondary_unit_is_rejected(): void
    {
        $response = $this->actingAs($this->manager)->post('/products', [
            'sku' => 'PRD-TEST-BARCODE',
            'barcode' => '999888777666',
            'name' => 'Clashing Barcode Product',
            'category_id' => $this->category->id,
            'base_unit_name' => 'Pcs',
            'purchase_price' => 1000,
            'default_selling_price' => 2000,
            'min_stock' => 5,
            'reorder_point' => 10,
            'units' => [
                [
                    'unit_name' => 'Dus',
                    'conversion_factor' => 20,
                    'barcode' => '999888777666', // SAME AS BASE BARCODE!
                    'selling_price' => 38000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('units.0.barcode');
    }

    public function test_manager_can_update_product_and_units(): void
    {
        $product = Product::first();

        $response = $this->actingAs($this->manager)->put("/products/{$product->id}", [
            'sku' => $product->sku,
            'name' => 'Updated Product Name',
            'category_id' => $product->category_id,
            'base_unit_name' => $product->base_unit_name,
            'purchase_price' => 3000,
            'default_selling_price' => 4000,
            'min_stock' => 15,
            'reorder_point' => 30,
            'units' => [
                [
                    'unit_name' => 'Karton Baru',
                    'conversion_factor' => 50,
                    'barcode' => '999111222333',
                    'selling_price' => 190000,
                ],
            ],
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'default_selling_price' => 4000,
        ]);
        $this->assertDatabaseHas('product_units', [
            'product_id' => $product->id,
            'unit_name' => 'Karton Baru',
            'conversion_factor' => 50,
        ]);
    }

    public function test_manager_can_soft_delete_product(): void
    {
        $product = Product::first();

        $this->actingAs($this->manager)
            ->delete("/products/{$product->id}")
            ->assertRedirect('/products');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}

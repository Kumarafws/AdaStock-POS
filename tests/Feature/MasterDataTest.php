<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('username', 'admin')->first();
        $this->manager = User::where('username', 'manager')->first();
        $this->cashier = User::where('username', 'cashier')->first();
    }

    public function test_manager_can_create_and_update_category(): void
    {
        $response = $this->actingAs($this->manager)->post('/categories', [
            'code' => 'CAT-BEV',
            'name' => 'Minuman Dingin',
            'description' => 'Aneka soda dan jus botol',
            'is_active' => true,
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'code' => 'CAT-BEV',
            'name' => 'Minuman Dingin',
        ]);

        $category = Category::where('code', 'CAT-BEV')->first();

        $updateResponse = $this->actingAs($this->manager)->put("/categories/{$category->id}", [
            'code' => 'CAT-BEV',
            'name' => 'Minuman Segar & Dingin',
        ]);

        $updateResponse->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Minuman Segar & Dingin',
        ]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::first();
        $category = $product->category;

        $response = $this->actingAs($this->manager)->delete("/categories/{$category->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_manager_can_create_brand(): void
    {
        $response = $this->actingAs($this->manager)->post('/brands', [
            'name' => 'Nestle',
            'description' => 'Produk susu dan kopi sachet',
            'is_active' => true,
        ]);

        $response->assertRedirect('/brands');
        $this->assertDatabaseHas('brands', [
            'name' => 'Nestle',
        ]);
    }

    public function test_manager_can_create_and_update_supplier(): void
    {
        $response = $this->actingAs($this->manager)->post('/suppliers', [
            'code' => 'SUP-099',
            'name' => 'PT Sumber Alfaria Pasifik',
            'contact_name' => 'Rudy Hartono',
            'phone' => '081234567890',
            'email' => 'supply@sumberalfaria.id',
            'address' => 'Jl. Kebon Jeruk No. 88, Jakarta Barat',
            'tax_id' => '01.999.888.7-001.000',
            'is_active' => true,
        ]);

        $response->assertRedirect('/suppliers');
        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP-099',
            'name' => 'PT Sumber Alfaria Pasifik',
        ]);

        $supplier = Supplier::where('code', 'SUP-099')->first();

        $updateResponse = $this->actingAs($this->manager)->put("/suppliers/{$supplier->id}", [
            'code' => 'SUP-099',
            'name' => 'PT Sumber Alfaria Pasifik Tbk',
            'contact_name' => 'Rudy Hartono MBA',
            'phone' => '081234567890',
        ]);

        $updateResponse->assertRedirect('/suppliers');
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'PT Sumber Alfaria Pasifik Tbk',
        ]);
    }

    public function test_cashier_cannot_access_category_brand_supplier_management(): void
    {
        $this->actingAs($this->cashier)->get('/categories')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/brands')->assertStatus(403);
        $this->actingAs($this->cashier)->get('/suppliers')->assertStatus(403);
    }
}

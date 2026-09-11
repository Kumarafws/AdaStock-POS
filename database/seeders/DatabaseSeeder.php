<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Locations
        $warehouse = Location::create([
            'code' => 'WHS-01',
            'name' => 'Gudang Distribusi Pusat',
            'type' => LocationType::WAREHOUSE,
            'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
            'phone' => '021-4682001',
            'is_active' => true,
        ]);

        $store = Location::create([
            'code' => 'STR-01',
            'name' => 'Toko Cabang Utama Jakarta',
            'type' => LocationType::STORE,
            'address' => 'Jl. Sudirman No. 45, Jakarta Pusat',
            'phone' => '021-5720101',
            'is_active' => true,
        ]);

        $quarantine = Location::create([
            'code' => 'QRN-01',
            'name' => 'Gudang Karantina & Rusak',
            'type' => LocationType::QUARANTINE,
            'address' => 'Zona Karantina - Gudang Pusat',
            'phone' => '021-4682002',
            'is_active' => true,
        ]);

        // 2. Seed Users
        User::create([
            'name' => 'Super Administrator',
            'username' => 'admin',
            'email' => 'admin@adastock.local',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'supervisor_pin' => Hash::make('123456'),
            'status' => 'active',
            'phone' => '081200000001',
        ]);

        User::create([
            'name' => 'Budi Pratama',
            'username' => 'manager',
            'email' => 'manager@adastock.local',
            'password' => Hash::make('password'),
            'role' => UserRole::MANAGER,
            'supervisor_pin' => Hash::make('654321'),
            'assigned_store_id' => $store->id,
            'status' => 'active',
            'phone' => '081200000002',
        ]);

        User::create([
            'name' => 'Siti Rahma',
            'username' => 'cashier',
            'email' => 'cashier@adastock.local',
            'password' => Hash::make('password'),
            'role' => UserRole::CASHIER,
            'assigned_store_id' => $store->id,
            'status' => 'active',
            'phone' => '081200000003',
        ]);

        // 3. Seed Categories
        $catFnb = Category::create([
            'code' => 'CAT-FNB',
            'name' => 'Makanan & Minuman',
            'description' => 'Produk pangan kemasan, mi instan, minuman siap saji, dan bumbu dapur.',
            'is_active' => true,
        ]);

        $catHpc = Category::create([
            'code' => 'CAT-HPC',
            'name' => 'Perawatan Tubuh & Kebersihan',
            'description' => 'Sabun mandi, sampo, pasta gigi, dan kosmetik ringan.',
            'is_active' => true,
        ]);

        $catHse = Category::create([
            'code' => 'CAT-HSE',
            'name' => 'Kebutuhan Rumah Tangga',
            'description' => 'Detergen, pembersih lantai, kantong plastik, dan perlengkapan rumah.',
            'is_active' => true,
        ]);

        $catSnk = Category::create([
            'code' => 'CAT-SNK',
            'name' => 'Biskuit & Camilan',
            'description' => 'Biskuit, wafer, keripik, dan snack ringan.',
            'is_active' => true,
        ]);

        // 4. Seed Brands
        $brandIndofood = Brand::create(['name' => 'Indofood', 'description' => 'Produsen mi dan pangan terkemuka']);
        $brandUnilever = Brand::create(['name' => 'Unilever', 'description' => 'Consumer goods & personal care']);
        $brandUltrajaya = Brand::create(['name' => 'Ultra Jaya', 'description' => 'Produk susu UHT & minuman']);
        $brandWings = Brand::create(['name' => 'Wings Group', 'description' => 'Kebutuhan rumah tangga dan sabun']);
        $brandMayora = Brand::create(['name' => 'Mayora', 'description' => 'Biskuit dan minuman kemasan']);

        // 5. Seed Suppliers
        Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Indomarco Adi Prima',
            'contact_name' => 'Hendro Santoso',
            'phone' => '08119876543',
            'email' => 'order@indomarco.co.id',
            'address' => 'Kawasan Pergudangan Pluit, Jakarta Utara',
            'tax_id' => '01.234.567.8-012.000',
            'is_active' => true,
        ]);

        Supplier::create([
            'code' => 'SUP-002',
            'name' => 'PT Unilever Indonesia Tbk',
            'contact_name' => 'Diana Putri',
            'phone' => '08128765432',
            'email' => 'retail.supply@unilever.com',
            'address' => 'BSD City Kav. Commercial Park, Tangerang',
            'tax_id' => '01.345.678.9-023.000',
            'is_active' => true,
        ]);

        Supplier::create([
            'code' => 'SUP-003',
            'name' => 'PT Ultra Jaya Milk Industry Tbk',
            'contact_name' => 'Bambang Irawan',
            'phone' => '08137654321',
            'email' => 'distribusi@ultrajaya.co.id',
            'address' => 'Jl. Raya Cimareme No. 131, Padalarang, Bandung Barat',
            'tax_id' => '01.456.789.0-034.000',
            'is_active' => true,
        ]);

        // 6. Seed Products with Multi-Units (UOM)
        // Product 1: Indomie Goreng
        $p1 = Product::create([
            'sku' => 'PRD-IND-001',
            'barcode' => '089686010018',
            'name' => 'Indomie Mi Goreng Spesial 85g',
            'category_id' => $catFnb->id,
            'brand_id' => $brandIndofood->id,
            'base_unit_name' => 'Pcs',
            'purchase_price' => 2650,
            'default_selling_price' => 3100,
            'min_stock' => 50,
            'reorder_point' => 120,
            'description' => 'Mi instan goreng legendaris dengan bumbu gurih khas Indonesia.',
            'is_active' => true,
        ]);
        ProductUnit::create([
            'product_id' => $p1->id,
            'unit_name' => 'Dus',
            'conversion_factor' => 40,
            'barcode' => '089686010049',
            'selling_price' => 120000,
        ]);

        // Product 2: Ultra Milk
        $p2 = Product::create([
            'sku' => 'PRD-UM-002',
            'barcode' => '899274191102',
            'name' => 'Susu UHT Ultra Milk Cokelat 250ml',
            'category_id' => $catFnb->id,
            'brand_id' => $brandUltrajaya->id,
            'base_unit_name' => 'Kotak',
            'purchase_price' => 5200,
            'default_selling_price' => 6500,
            'min_stock' => 24,
            'reorder_point' => 48,
            'description' => 'Susu segar rasa cokelat kaya kalsium dan vitamin.',
            'is_active' => true,
        ]);
        ProductUnit::create([
            'product_id' => $p2->id,
            'unit_name' => 'Karton',
            'conversion_factor' => 24,
            'barcode' => '899274191124',
            'selling_price' => 150000,
        ]);

        // Product 3: Sabun Lifebuoy
        $p3 = Product::create([
            'sku' => 'PRD-LFB-003',
            'barcode' => '899999901451',
            'name' => 'Sabun Mandi Batang Lifebuoy Total 10 85g',
            'category_id' => $catHpc->id,
            'brand_id' => $brandUnilever->id,
            'base_unit_name' => 'Batang',
            'purchase_price' => 3600,
            'default_selling_price' => 4500,
            'min_stock' => 20,
            'reorder_point' => 40,
            'description' => 'Sabun antibakteri perlindungan kuman keluarga.',
            'is_active' => true,
        ]);
        ProductUnit::create([
            'product_id' => $p3->id,
            'unit_name' => 'Pak',
            'conversion_factor' => 4,
            'barcode' => '899999901454',
            'selling_price' => 17000,
        ]);

        // Product 4: Biskuit Roma Kelapa
        $p4 = Product::create([
            'sku' => 'PRD-RMA-004',
            'barcode' => '899600130101',
            'name' => 'Biskuit Roma Kelapa 300g',
            'category_id' => $catSnk->id,
            'brand_id' => $brandMayora->id,
            'base_unit_name' => 'Bungkus',
            'purchase_price' => 9500,
            'default_selling_price' => 11500,
            'min_stock' => 12,
            'reorder_point' => 24,
            'description' => 'Biskuit renyah gurih rasa kelapa asli pilihan.',
            'is_active' => true,
        ]);
        ProductUnit::create([
            'product_id' => $p4->id,
            'unit_name' => 'Dus',
            'conversion_factor' => 12,
            'barcode' => '899600130112',
            'selling_price' => 132000,
        ]);

        // 7. Seed Initial Inventory Balances & Movements via InventoryService
        /** @var \App\Services\InventoryService $inventoryService */
        $inventoryService = app(\App\Services\InventoryService::class);
        $adminUser = User::where('role', UserRole::ADMIN)->first();

        // Stock in Warehouse (WHS-01)
        $inventoryService->recordMovement(
            product: $p1,
            location: $warehouse,
            quantityInBaseUnit: 800, // 20 Dus
            type: \App\Enums\MovementType::PURCHASE_RECEIPT,
            refType: 'GoodsReceipt',
            refNumber: 'GR/20260901/0001',
            notes: 'Penerimaan Stok Awal Gudang Pusat dari Supplier PT Indomarco Adi Prima',
            actor: $adminUser
        );

        $inventoryService->recordMovement(
            product: $p2,
            location: $warehouse,
            quantityInBaseUnit: 240, // 10 Karton
            type: \App\Enums\MovementType::PURCHASE_RECEIPT,
            refType: 'GoodsReceipt',
            refNumber: 'GR/20260901/0002',
            notes: 'Penerimaan Stok Awal Gudang Pusat dari Supplier PT Ultra Jaya',
            actor: $adminUser
        );

        $inventoryService->recordMovement(
            product: $p3,
            location: $warehouse,
            quantityInBaseUnit: 200, // 50 Pak
            type: \App\Enums\MovementType::PURCHASE_RECEIPT,
            refType: 'GoodsReceipt',
            refNumber: 'GR/20260901/0003',
            notes: 'Penerimaan Stok Awal Gudang Pusat dari PT Unilever',
            actor: $adminUser
        );

        $inventoryService->recordMovement(
            product: $p4,
            location: $warehouse,
            quantityInBaseUnit: 120, // 10 Dus
            type: \App\Enums\MovementType::PURCHASE_RECEIPT,
            refType: 'GoodsReceipt',
            refNumber: 'GR/20260901/0004',
            notes: 'Penerimaan Stok Awal Gudang Pusat dari Mayora',
            actor: $adminUser
        );

        // Stock in Store (STR-01)
        $inventoryService->recordMovement(
            product: $p1,
            location: $store,
            quantityInBaseUnit: 120, // 3 Dus
            type: \App\Enums\MovementType::TRANSFER_IN,
            refType: 'StockTransfer',
            refNumber: 'TRF/20260905/0001',
            notes: 'Transfer Alokasi Display Toko Cabang Utama',
            actor: $adminUser
        );

        $inventoryService->recordMovement(
            product: $p2,
            location: $store,
            quantityInBaseUnit: 48, // 2 Karton
            type: \App\Enums\MovementType::TRANSFER_IN,
            refType: 'StockTransfer',
            refNumber: 'TRF/20260905/0002',
            notes: 'Transfer Alokasi Chiller Toko Cabang Utama',
            actor: $adminUser
        );

        $inventoryService->recordMovement(
            product: $p3,
            location: $store,
            quantityInBaseUnit: 32, // 8 Pak
            type: \App\Enums\MovementType::TRANSFER_IN,
            refType: 'StockTransfer',
            refNumber: 'TRF/20260905/0003',
            notes: 'Transfer Alokasi Rak Toiletries Toko',
            actor: $adminUser
        );

        $inventoryService->recordMovement(
            product: $p4,
            location: $store,
            quantityInBaseUnit: 24, // 2 Dus
            type: \App\Enums\MovementType::TRANSFER_IN,
            refType: 'StockTransfer',
            refNumber: 'TRF/20260905/0004',
            notes: 'Transfer Alokasi Rak Biskuit Toko',
            actor: $adminUser
        );

        // Damaged item in Quarantine (QRN-01)
        $inventoryService->recordMovement(
            product: $p1,
            location: $quarantine,
            quantityInBaseUnit: 5,
            type: \App\Enums\MovementType::TRANSFER_IN,
            refType: \App\Models\StockAdjustment::class,
            refNumber: 'ADJ/20260910/0001',
            notes: 'Penerimaan Karantina barang rusak dari Toko Cabang Utama (kemasan sobek)',
            actor: $adminUser
        );
    }
}

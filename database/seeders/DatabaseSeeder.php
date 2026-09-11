<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use App\Enums\UserRole;
use App\Models\Location;
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
        // Admin
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

        // Manager
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

        // Cashier
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
    }
}

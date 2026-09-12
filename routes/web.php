<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Manager Only Routes (Master Data Management)
    Route::middleware('role:admin,manager')->group(function () {
        // Product Management (Write)
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');

        // Categories, Brands, Suppliers
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('brands', BrandController::class)->except(['create', 'show', 'edit']);
        Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);

        // Locations
        Route::get('/locations', function () {
            $locations = \App\Models\Location::withCount('users')->get();
            return view('locations.index', compact('locations'));
        })->name('locations.index');

        // Stock Ledger & Adjustments (Admin & Manager)
        Route::get('/inventory/ledger', [InventoryController::class, 'ledger'])->name('inventory.ledger');
        Route::get('/adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
        Route::get('/adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
        Route::post('/adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');

        // Purchasing & Procurement (Admin & Manager)
        Route::prefix('purchasing')->name('purchasing.')->group(function () {
            // Purchase Orders
            Route::get('/orders', [PurchaseOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [PurchaseOrderController::class, 'create'])->name('orders.create');
            Route::post('/orders', [PurchaseOrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [PurchaseOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('orders.cancel');

            // Goods Receipts (Penerimaan Barang)
            Route::get('/receipts', [GoodsReceiptController::class, 'index'])->name('receipts.index');
            Route::get('/receipts/create', [GoodsReceiptController::class, 'create'])->name('receipts.create');
            Route::post('/receipts', [GoodsReceiptController::class, 'store'])->name('receipts.store');
            Route::get('/receipts/{receipt}', [GoodsReceiptController::class, 'show'])->name('receipts.show');

            // Purchase Returns (Retur Supplier)
            Route::get('/returns', [PurchaseReturnController::class, 'index'])->name('returns.index');
            Route::get('/returns/create', [PurchaseReturnController::class, 'create'])->name('returns.create');
            Route::post('/returns', [PurchaseReturnController::class, 'store'])->name('returns.store');
            Route::get('/returns/{return}', [PurchaseReturnController::class, 'show'])->name('returns.show');
        });
    });

    // Product & Inventory Read Routes (All authenticated roles: Cashier, Manager, Admin)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show')->whereNumber('product');

    // Real-time Stock Monitoring (Scoped for Cashier)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/check-stock', [InventoryController::class, 'checkStock'])->name('inventory.check-stock');

    // Admin-Only Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', function () {
            return view('admin.users.index');
        })->name('users.index');
    });

    // Cashier Routes (POS & Shift)
    Route::middleware('role:cashier,admin,manager')->group(function () {
        Route::get('/pos', function () {
            return view('pos.index');
        })->name('pos.index');

        Route::get('/shifts', function () {
            return view('shifts.index');
        })->name('shifts.index');
    });
});

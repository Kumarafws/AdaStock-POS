<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->nullOnDelete();
            $table->string('unit_name', 50); // e.g. "Dus", "Pak", "Pcs"
            $table->integer('conversion_factor')->default(1);
            $table->integer('ordered_quantity'); // Qty in order unit (e.g. 10 Dus)
            $table->integer('ordered_quantity_base'); // Qty in Base Unit (e.g. 400 Pcs)
            $table->integer('received_quantity_base')->default(0); // Physically received Base Units
            $table->decimal('unit_cost', 15, 2); // Price per order unit
            $table->decimal('base_unit_cost', 15, 2); // Price per Base Unit
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index(['purchase_order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};

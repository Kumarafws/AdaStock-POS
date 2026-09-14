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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->nullOnDelete();
            $table->string('unit_name', 50);
            $table->integer('conversion_factor')->default(1);
            $table->integer('quantity');
            $table->integer('quantity_base');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('base_unit_price', 15, 2);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('base_unit_cost', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('cogs_total', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

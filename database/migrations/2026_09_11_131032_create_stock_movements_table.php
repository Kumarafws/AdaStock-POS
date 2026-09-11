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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->integer('quantity'); // Signed: positive (+) for IN, negative (-) for OUT
            $table->integer('balance_before'); // Stock snapshot immediately before
            $table->integer('balance_after');  // Stock snapshot immediately after
            $table->string('movement_type', 50); // MovementType enum value
            $table->string('reference_type')->nullable(); // Polymorphic model class
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number', 100)->nullable(); // e.g. ADJ/20260911/0001
            $table->decimal('cogs_per_unit', 15, 2)->default(0); // Cost per base unit at time of movement
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'location_id']);
            $table->index('movement_type');
            $table->index('created_at');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 10); // 'in' or 'out'
            $table->integer('quantity'); // Positive integer in Base Unit
            $table->string('reason', 50); // AdjustmentReason enum value
            $table->string('action_type', 30)->default('normal'); // 'normal', 'to_quarantine', 'disposal'
            $table->decimal('cogs_at_time', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['location_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};

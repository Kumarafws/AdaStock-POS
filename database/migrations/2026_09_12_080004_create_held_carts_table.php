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
        Schema::create('held_carts', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 100);
            $table->foreignId('cashier_shift_id')->constrained('cashier_shifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('customer_name', 100)->nullable();
            $table->json('cart_items');
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('held_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('held_carts');
    }
};

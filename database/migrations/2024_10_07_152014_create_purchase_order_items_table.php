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
            $table->unsignedBigInteger('purchase_order_id'); // Foreign key to purchase_orders
            $table->unsignedBigInteger('product_id'); // Foreign key to products
            $table->decimal('quantity', 10, 2); // Store in kg
            $table->decimal('price', 15, 2); // Store in kg
            $table->decimal('total_price', 20, 2); // Total price for this line item
            $table->timestamps();
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

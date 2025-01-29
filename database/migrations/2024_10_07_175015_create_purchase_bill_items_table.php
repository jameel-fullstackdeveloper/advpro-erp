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
        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('purchase_items')->onDelete('cascade');
            $table->decimal('quantity', 15, 2);
            $table->decimal('deduction', 15, 2)->default(0.00);
            $table->decimal('net_quantity', 15, 2)->default(0.00);
            $table->decimal('price', 15, 2);
            $table->decimal('gross_amount', 15, 2)->default(0.00);
            $table->decimal('sales_tax_rate', 15, 2)->default(0.00);
            $table->decimal('sales_tax_amount', 15, 2)->default(0.00);
            $table->decimal('withholding_tax_rate', 15, 2)->default(0.00);
            $table->decimal('withholding_tax_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_items');
    }
};

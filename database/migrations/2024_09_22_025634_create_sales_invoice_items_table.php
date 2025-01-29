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
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->integer('sales_invoice_id'); // Link to Sales Invoice
            $table->integer('product_id'); // Link to Product
            $table->integer('quantity'); // Quantity of this product in the invoice
            $table->decimal('unit_price', 15, 4); // Price per unit of the product
            $table->decimal('net_amount', 15, 2)->default(0.00); // gross amount

            //discounts
            $table->decimal('discount_rate', 15, 2)->default(0.00); // Discount applied to the item
            $table->string('discount_type')->nullable(); // brokery method
            $table->decimal('discount_amount', 15, 2)->default(0.00); // Discount applied to the item
            $table->decimal('discount_per_bag_rate', 15, 2)->default(0.00); // Discount applied to the item
            $table->decimal('discount_per_bag_amount', 15, 2)->default(0.00); // Discount applied to the item
            $table->decimal('amount_excl_tax', 15, 2)->default(0.00); // Discount applied to the item

            // Sales tax fields
            $table->decimal('sales_tax_rate', 5, 2)->default(0.00); // Sales tax rate (e.g., 10.00 for 10%)
            $table->string('sales_tax_type')->nullable(); // brokery method
            $table->decimal('sales_tax_amount', 15, 2)->default(0.00); // Calculated sales tax amount

            // Further sales tax fields
            $table->decimal('further_sales_tax_rate', 5, 2)->default(0.00); // Further sales tax rate (e.g., 3.00 for 3%)
            $table->decimal('further_sales_tax_amount', 15, 2)->default(0.00); // Calculated further sales tax amount

            // advance  tax fields
            $table->decimal('advance_wht_rate', 5, 2)->default(0.00);
            $table->decimal('advance_wht_amount', 15, 2)->default(0.00);


            // Net amount (total with taxes)
            $table->decimal('amount_incl_tax', 15, 2)->default(0.00); // Final amount including all taxes

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};

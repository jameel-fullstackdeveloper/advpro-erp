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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id'); // Links to Sales Orders
            $table->string('invoice_number')->unique(); // Unique Invoice Number
            $table->date('invoice_date'); // Invoice date
            $table->integer('invoice_due_days')->default(0);
            $table->integer('customer_id')->nullable();
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->integer('is_weighbridge')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('financial_year_id')->nullable();
            $table->integer('freight_credit_to')->nullable();
            $table->integer('broker_id')->nullable();
            $table->decimal('broker_rate', 15, 2)->default(0.00);
            $table->string('calculation_method')->nullable(); // brokery method
            $table->decimal('broker_amount', 15, 2)->default(0.00);
            $table->string('comments')->nullable();
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

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
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->unsignedBigInteger('vendor_id'); // Assuming vendor is stored in the chartofaccounts table
            $table->unsignedBigInteger('order_id'); // Linked to the purchase orders
            $table->date('bill_date');
            $table->integer('bill_due_days')->default(0);
            $table->string('vehicle_no')->nullable;
            $table->decimal('freight', 15, 2);
            $table->string('delivery_mode')->nullable;
            $table->integer('broker_id')->nullable();
            $table->decimal('broker_rate', 15, 2)->default(0.00);
            $table->decimal('broker_amount', 15, 2)->default(0.00);
            $table->string('status')->default('init');
            $table->integer('is_weighbridge')->nullable();
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->integer('financial_year_id')->nullable();
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
        Schema::dropIfExists('purchase_bills');
    }
};

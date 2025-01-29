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
        Schema::create('delivery_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained(); // Links to Sales Orders
            $table->string('challan_number')->unique(); // Unique Challan Number
            $table->date('delivery_date'); // Delivery date
            $table->string('driver_name')->nullable(); // Driver details
            $table->string('vehicle_number')->nullable(); // Vehicle number
            $table->enum('status', ['created', 'in_transit', 'delivered'])->default('created'); // Status of delivery
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
        Schema::dropIfExists('sales_order_items');
    }
};

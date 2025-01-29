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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id'); // Links to Customers table
            $table->string('order_number')->unique(); // Unique Order Number
            $table->date('order_date'); // Order date
            $table->string('farm_name')->nullable();
            $table->string('farm_address')->nullable();
            $table->string('farm_supervisor_mobile')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('vehicle_fare')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'delivered', 'invoiced', 'canceled'])->default('pending'); // Order status
            $table->unsignedBigInteger('company_id');
            $table->integer('financial_year_id')->nullable();
            $table->string('order_comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->integer('created')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};

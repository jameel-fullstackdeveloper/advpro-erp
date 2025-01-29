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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->date('order_date');
            $table->string('order_number');
            $table->unsignedBigInteger('vendor_id'); // Foreign key to chartofaccount
            $table->string('status')->default('init');
            $table->string('delivery_mode')->nullable();
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->integer('credit_days')->nullable();
            $table->string('comments')->nullable();
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
        Schema::dropIfExists('purchase_orders');
    }
};

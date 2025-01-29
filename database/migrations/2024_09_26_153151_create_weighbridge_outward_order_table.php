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
        Schema::create('weighbridge_outward_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weighbridge_outward_id')->constrained()->onDelete('cascade'); // Link to weighbridge outward
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade'); // Link to sales order
            $table->decimal('order_weight', 8, 2); // Weight assigned to this sales order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weighbridge_outward_order');
    }

};

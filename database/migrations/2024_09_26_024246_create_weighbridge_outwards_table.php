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
        Schema::create('weighbridge_outwards', function (Blueprint $table) {
            $table->id();
            $table->string('truck_number');
            $table->integer('first_weight'); // Tare weight
            $table->integer('second_weight')->nullable(); // Gross weight
            $table->integer('net_weight')->nullable(); // Net weight (calculated as second_weight - first_weight)
            $table->string('driver_name')->nullable();
            $table->string('driver_mobile')->nullable();
            $table->integer('status')->default(0); // Status for tracking (e.g., pending, completed)
            $table->string('driveroption')->nullable();
            $table->integer('company_id')->nullable(); // Company-specific ID (optional)
            $table->integer('financial_year_id')->nullable(); // Financial year (optional)
            $table->foreignId('created_by')->constrained('users'); // User who created the record
            $table->foreignId('updated_by')->nullable()->constrained('users'); // User who last updated the record
            $table->timestamp('first_weight_datetime')->nullable();  // Date and time for first weight (tare)
            $table->timestamp('second_weight_datetime')->nullable(); // Date and time for second weight (gross)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weighbridge_outwards');
    }
};

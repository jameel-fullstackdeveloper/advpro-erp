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
        Schema::create('weighbridge_inwards', function (Blueprint $table) {
            $table->id();
            $table->string('truck_number');
            $table->string('billty_number')->nullable();
            $table->integer('freight')->nullable();
            $table->integer('total_bags')->nullable();
            $table->integer('party_gross_weight')->nullable();
            $table->integer('party_tare_weight')->nullable();
            $table->integer('party_net_weight')->nullable();
            $table->integer('first_weight')->nullable();
            $table->integer('second_weight')->nullable();
            $table->integer('net_weight')->nullable();
            $table->string('driveroption');
            $table->integer('status')->default(0); // Status for tracking (e.g., pending, completed)
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->integer('financial_year_id')->nullable();
            $table->timestamp('first_weight_datetime')->nullable();  // Date and time for first weight (tare)
            $table->timestamp('second_weight_datetime')->nullable(); // Date and time for second weight (gross)
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
        Schema::dropIfExists('weighbridge_inwards');
    }
};

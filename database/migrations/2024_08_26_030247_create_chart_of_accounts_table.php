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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('balance', 15, 2)->default(0); // Balance field with a default value of 0
            $table->string('drcr', 3)->nullable(); // 'dr' for debit, 'cr' for credi
            $table->foreignId('group_id')->nullable();
            $table->string('is_customer_vendor')->nullable(); // Can store values like 'customer', 'vendor', etc.
            $table->foreignId('company_id')->nullable(); // Company ID
            $table->foreignId('created_by')->nullable(); // Add the createdn user id field
            $table->foreignId('updated_by')->nullable(); // Add the updated user id  field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};

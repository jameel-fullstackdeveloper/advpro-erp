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
        Schema::create('chart_of_accounts_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Asset, Liability, etc.
            $table->string('type_id'); // Type of account head: Asset, Liability, Expense, Revenue, Equity
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
        Schema::dropIfExists('chart_of_accounts_heads');
    }
};

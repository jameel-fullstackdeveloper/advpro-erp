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
        Schema::create('customer_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id'); // Foreign key to the chart_of_accounts table
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('cnic')->nullable();
            $table->string('strn')->nullable();
            $table->string('ntn')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('bonus')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->string('payment_terms')->nullable(); // e.g., Net 30, Net 60, etc.
            $table->integer('financial_year_id')->nullable();
            $table->decimal('broker_rate', 8, 2)->default(0);
            $table->string('avatar')->nullable();
            $table->foreignId('company_id')->nullable();
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
        Schema::dropIfExists('customer_details');
    }
};

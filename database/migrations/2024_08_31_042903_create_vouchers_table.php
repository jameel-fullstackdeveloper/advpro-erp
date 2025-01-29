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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_type'); // e.g., payment, receipt, journal
            $table->date('date');
            $table->string('reference_number')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // e.g., pending, approved, rejected
            $table->integer('financial_year_id')->nullable();
            $table->string('image_path')->nullable();
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
        Schema::dropIfExists('vouchers');
    }
};

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
        Schema::create('pruchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number');
            $table->date('return_date');
            $table->unsignedBigInteger('vendor_id');
            $table->string('status')->default('posted');
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
        Schema::dropIfExists('pruchase_returns');
    }
};

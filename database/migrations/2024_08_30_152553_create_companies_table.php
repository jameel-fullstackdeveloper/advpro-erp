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
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Company name
            $table->string('abv')->nullable(); // Company name
            $table->string('address')->nullable(); // Company address, nullable if not always required
            $table->string('email')->unique(); // Company email, unique constraint
            $table->string('phone')->nullable(); // Company phone number, nullable
            $table->string('strn')->nullable(); // Company name
            $table->string('ntn')->nullable(); // Company name
            $table->string('type')->nullable(); // Company phone number, nullable
            $table->string('avatar')->nullable(); // Company email, unique constraint
            $table->integer('created_by')->default(1); // Reference to user who created the company
            $table->integer('updated_by')->default(1);; // Reference to the user who last updated the company record
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

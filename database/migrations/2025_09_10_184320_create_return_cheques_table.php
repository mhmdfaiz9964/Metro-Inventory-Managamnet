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
        Schema::create('return_cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_no'); // Original cheque number
            $table->string('return_cheque_no')->unique(); // Auto-generated return cheque number
            $table->date('return_date');
            $table->decimal('amount', 15, 2);
            $table->string('cheque_bank'); // Bank name
            $table->text('return_reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_cheques');
    }
};
